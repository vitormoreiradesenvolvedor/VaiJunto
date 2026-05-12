<?php

namespace App\Http\Controllers;

use App\Events\FixedRoutePaused;
use App\Events\NewFixedRouteOffer;
use App\Events\NewRideRequestForDriver;
use App\Events\RideCancelledByDriver;
use App\Models\FixedRoute;
use App\Models\RideRequest;
use App\Services\NotificationService;
use App\Services\RideService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FixedRouteController extends Controller
{
    public function __construct(
        private NotificationService $notificationService,
        private RideService $rideService,
    ) {}

    /** Formulário de criação (motorista) */
    public function create(): View
    {
        return view('routes.create', [
            'vehicle' => auth()->user()->vehicle,
            'mapsKey' => config('services.google.maps_key'),
        ]);
    }

    /** Salva rota fixa (motorista) */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'origin'             => 'required|string',
            'destination'        => 'required|string',
            'origin_coords'      => 'required|string',
            'destination_coords' => 'required|string',
            'departure_time'     => 'required|date_format:H:i',
            'days_of_week'       => 'required|array|min:1',
            'days_of_week.*'     => 'integer|between:0,6',
            'available_seats'    => 'required|integer|min:1|max:8',
        ]);

        $fixedRoute = FixedRoute::create([
            ...$data,
            'driver_id'  => auth()->id(),
            'vehicle_id' => auth()->user()->vehicle?->id,
            'status'     => 'active',
        ]);

        $fixedRoute->load('driver');
        try {
            NewFixedRouteOffer::dispatch($fixedRoute);
        } catch (\Throwable) { /* broadcast failure não bloqueia */ }

        return redirect()->route('dashboard')
            ->with('success', 'Rota fixa criada! Passageiros poderão encontrá-la e solicitar vagas.');
    }

    /** Detalhe de uma rota fixa (motorista) */
    public function show(FixedRoute $fixedRoute): View
    {
        abort_if($fixedRoute->driver_id !== auth()->id(), 403);

        return view('routes.show', [
            'route'   => $fixedRoute->load(['requests.passenger', 'requests.ride', 'vehicle']),
            'mapsKey' => config('services.google.maps_key'),
        ]);
    }

    /** Pausar/reativar rota (motorista) */
    public function toggleStatus(FixedRoute $fixedRoute): JsonResponse
    {
        abort_if($fixedRoute->driver_id !== auth()->id(), 403);

        $newStatus = $fixedRoute->status === 'active' ? 'paused' : 'active';
        $fixedRoute->update(['status' => $newStatus]);

        if ($newStatus === 'paused') {
            $passengers = $fixedRoute->requests()
                ->where('status', 'accepted')
                ->with('passenger')
                ->get()
                ->pluck('passenger')
                ->filter();

            foreach ($passengers as $passenger) {
                try { FixedRoutePaused::dispatch($fixedRoute, $passenger); } catch (\Throwable) {}
            }
        }

        return response()->json(['status' => $newStatus]);
    }

    /** Passageiro solicita vaga na rota fixa */
    public function join(FixedRoute $fixedRoute): JsonResponse
    {
        abort_if($fixedRoute->driver_id === auth()->id(), 403);
        abort_if($fixedRoute->status !== 'active', 422);

        $passenger = auth()->user();

        // Evita duplicata: já tem solicitação pendente/aceita para esta rota
        $already = RideRequest::where('fixed_route_id', $fixedRoute->id)
            ->where('passenger_id', $passenger->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();
        abort_if($already, 422, 'Você já solicitou vaga nesta rota.');

        $nextRide = $fixedRoute->nextOccurrence();

        $rideRequest = RideRequest::create([
            'passenger_id'       => $passenger->id,
            'fixed_route_id'     => $fixedRoute->id,
            'origin'             => $fixedRoute->origin,
            'destination'        => $fixedRoute->destination,
            'origin_coords'      => $fixedRoute->origin_coords ?? '0,0',
            'destination_coords' => $fixedRoute->destination_coords ?? '0,0',
            'scheduled_for'      => $nextRide,
            'seats_needed'       => 1,
            'status'             => 'pending',
        ]);

        $rideRequest->load('passenger');

        // Notifica o motorista da rota
        $driver = $fixedRoute->driver;
        try {
            $this->notificationService->notify($driver, 'new_ride_request', [
                'ride_request_id' => $rideRequest->id,
            ]);
            NewRideRequestForDriver::dispatch($rideRequest, $driver);
        } catch (\Throwable) { /* broadcast failure não bloqueia o fluxo */ }

        return response()->json([
            'message'   => 'Solicitação enviada! O motorista será notificado.',
            'track_url' => route('rides.track', $rideRequest),
        ], 201);
    }

    /** Motorista aceita solicitação de rota fixa */
    public function acceptRequest(FixedRoute $fixedRoute, RideRequest $rideRequest): JsonResponse
    {
        abort_if($fixedRoute->driver_id !== auth()->id(), 403);
        abort_if($rideRequest->fixed_route_id !== $fixedRoute->id, 422);

        $driver = auth()->user();
        try {
            $this->rideService->accept($rideRequest, $driver);
        } catch (\Throwable) {
            // fallback: apenas atualiza status sem criar ride
            $rideRequest->update(['status' => 'accepted']);
        }

        return response()->json(['message' => 'Solicitação aceita.']);
    }

    /** Motorista encerra rota fixa permanentemente */
    public function cancel(FixedRoute $fixedRoute, Request $request): JsonResponse
    {
        abort_if($fixedRoute->driver_id !== auth()->id(), 403);

        $reason = trim((string) $request->input('cancel_reason', ''));
        if (!$reason) {
            return response()->json(['message' => 'Informe o motivo do encerramento.'], 422);
        }

        try {
            foreach ($fixedRoute->requests()->where('status', 'accepted')->with('ride')->get() as $req) {
                if (!$req->ride || !in_array($req->ride->status, ['pending', 'accepted', 'in_progress'])) {
                    continue;
                }
                try {
                    $this->rideService->cancel($req->ride, $reason);
                    try { RideCancelledByDriver::dispatch($req->ride); } catch (\Throwable) {}
                } catch (\Throwable) {
                    try { $req->ride->update(['status' => 'cancelled', 'cancel_reason' => $reason]); } catch (\Throwable) {}
                    try { $req->update(['status' => 'cancelled']); } catch (\Throwable) {}
                }
            }

            $fixedRoute->update(['status' => 'cancelled']);
            $fixedRoute->requests()->whereIn('status', ['pending', 'accepted'])->update(['status' => 'cancelled']);
        } catch (\Throwable $e) {
            \Log::error('FixedRouteController::cancel error: ' . $e->getMessage());
            return response()->json(['message' => 'Erro ao encerrar: ' . $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Rota encerrada.']);
    }

    /** Retorna solicitações pendentes (polling fallback) */
    public function pendingRequests(FixedRoute $fixedRoute): JsonResponse
    {
        abort_if($fixedRoute->driver_id !== auth()->id(), 403);

        $requests = $fixedRoute->requests()
            ->with('passenger')
            ->where('status', 'pending')
            ->get()
            ->map(fn ($r) => [
                'id'            => $r->id,
                'scheduled_for' => $r->scheduled_for?->toIso8601String(),
                'passenger'     => [
                    'name'   => $r->passenger->name,
                    'avatar' => $r->passenger->avatar,
                ],
            ]);

        return response()->json(['requests' => $requests]);
    }

    /** Motorista recusa solicitação de rota fixa */
    public function rejectRequest(FixedRoute $fixedRoute, RideRequest $rideRequest): JsonResponse
    {
        abort_if($fixedRoute->driver_id !== auth()->id(), 403);
        abort_if($rideRequest->fixed_route_id !== $fixedRoute->id, 422);

        $rideRequest->update(['status' => 'rejected']);

        return response()->json(['message' => 'Solicitação recusada.']);
    }

    /** @deprecated use toggleStatus */
    public function pause(FixedRoute $fixedRoute): JsonResponse
    {
        abort_if($fixedRoute->driver_id !== auth()->id(), 403);
        $fixedRoute->update(['status' => 'paused']);
        return response()->json(['message' => 'Rota pausada.']);
    }
}
