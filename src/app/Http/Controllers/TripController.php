<?php

namespace App\Http\Controllers;

use App\Events\NewTripOffer;
use App\Events\TripRequestReceived;
use App\Models\RideRequest;
use App\Models\Trip;
use App\Services\RideService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function __construct(private RideService $rideService) {}

    public function create(): View
    {
        return view('trips.create', [
            'vehicle' => auth()->user()->vehicle,
            'mapsKey' => config('services.google.maps_key'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'origin'             => 'required|string',
            'destination'        => 'required|string',
            'origin_coords'      => 'required|string',
            'destination_coords' => 'required|string',
            'departs_at'         => 'required|date|after:now',
            'seats_total'        => 'required|integer|min:1|max:8',
        ]);

        $trip = Trip::create([
            ...$data,
            'driver_id'  => auth()->id(),
            'vehicle_id' => auth()->user()->vehicle?->id,
        ]);

        $trip->load('driver');
        try {
            NewTripOffer::dispatch($trip);
        } catch (\Throwable) { /* falha no broadcast não deve bloquear a criação */ }

        return redirect()->route('trips.show', $trip)
            ->with('success', 'Viagem publicada! Passageiros já podem solicitar vaga.');
    }

    public function show(Trip $trip): View
    {
        abort_if($trip->driver_id !== auth()->id(), 403);

        return view('trips.show', [
            'trip'    => $trip->load(['requests.passenger', 'vehicle']),
            'mapsKey' => config('services.google.maps_key'),
        ]);
    }

    public function cancel(Trip $trip): JsonResponse
    {
        abort_if($trip->driver_id !== auth()->id(), 403);
        abort_if(!in_array($trip->status, ['open', 'full']), 422);

        $trip->update(['status' => 'cancelled']);
        $trip->requests()->where('status', 'pending')->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Viagem cancelada.']);
    }

    public function join(Trip $trip): JsonResponse
    {
        abort_if($trip->driver_id === auth()->id(), 403);
        abort_if($trip->status !== 'open', 422);

        $already = RideRequest::where('trip_id', $trip->id)
            ->where('passenger_id', auth()->id())
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();
        abort_if($already, 422);

        $rideRequest = RideRequest::create([
            'passenger_id'       => auth()->id(),
            'trip_id'            => $trip->id,
            'origin'             => $trip->origin,
            'destination'        => $trip->destination,
            'origin_coords'      => $trip->origin_coords ?? '0,0',
            'destination_coords' => $trip->destination_coords ?? '0,0',
            'scheduled_for'      => $trip->departs_at,
            'seats_needed'       => 1,
            'status'             => 'pending',
        ]);

        $rideRequest->load('passenger');
        TripRequestReceived::dispatch($trip, $rideRequest);

        return response()->json([
            'message'   => 'Solicitação enviada!',
            'track_url' => route('rides.track', $rideRequest),
        ], 201);
    }

    public function acceptRequest(Trip $trip, RideRequest $rideRequest): JsonResponse
    {
        abort_if($trip->driver_id !== auth()->id(), 403);
        abort_if($rideRequest->trip_id !== $trip->id, 422);

        $ride = $this->rideService->accept($rideRequest, auth()->user());

        $acceptedCount = $trip->requests()->where('status', 'accepted')->count();
        if ($acceptedCount >= $trip->seats_total) {
            $trip->update(['status' => 'full']);
        }

        return response()->json($ride);
    }

    public function rejectRequest(Trip $trip, RideRequest $rideRequest): JsonResponse
    {
        abort_if($trip->driver_id !== auth()->id(), 403);
        abort_if($rideRequest->trip_id !== $trip->id, 422);

        $rideRequest->update(['status' => 'rejected']);

        return response()->json(['message' => 'Solicitação recusada.']);
    }
}
