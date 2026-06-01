<?php

namespace App\Http\Controllers;

use App\Events\DriverArrived;
use App\Events\DriverLocationUpdated;
use App\Events\PassengerBoarded;
use App\Events\RideCancelledByDriver;
use App\Events\RideCancelledByPassenger;
use App\Models\Ride;
use App\Models\RideRequest;
use App\Services\RideService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RideController extends Controller
{
    public function __construct(private RideService $rideService) {}

    public function create(): View
    {
        return view('rides.create', [
            'mapsKey' => config('services.google.maps_key'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'origin'              => 'required|string|min:3',
            'destination'        => 'required|string|min:3',
            'origin_coords'      => 'required|string',
            'destination_coords' => 'required|string',
            'scheduled_for'      => 'required|date',
            'seats_needed'       => 'required|integer|min:1',
        ]);

        foreach (['origin_coords', 'destination_coords'] as $field) {
            [$lat, $lng] = array_pad(explode(',', $data[$field]), 2, '0');
            if ((float) $lat === 0.0 && (float) $lng === 0.0) {
                return response()->json([
                    'errors' => ['origin' => ['Selecione os endereços a partir das sugestões do mapa.']],
                ], 422);
            }
        }

        $rideRequest = $this->rideService->request($data, $request->user());

        return response()->json([
            'id'        => $rideRequest->id,
            'track_url' => route('rides.track', $rideRequest),
        ], 201);
    }

    public function track(RideRequest $rideRequest): View
    {
        abort_if($rideRequest->passenger_id !== auth()->id(), 403);

        return view('rides.track', [
            'rideRequest' => $rideRequest->load('ride.driver.vehicle'),
            'mapsKey'     => config('services.google.maps_key'),
        ]);
    }

    public function status(RideRequest $rideRequest): JsonResponse
    {
        abort_if($rideRequest->passenger_id !== auth()->id(), 403);

        $rideRequest->load('ride.driver.vehicle');
        $ride = $rideRequest->ride;

        $vehicle = null;
        if ($ride) {
            $v = $ride->vehicle ?? $ride->driver?->vehicle;
            $vehicle = $v ? ['model' => $v->model, 'color' => $v->color, 'plate' => $v->plate] : null;
        }

        return response()->json([
            'request_status' => $rideRequest->status,
            'ride' => $ride ? [
                'id'                   => $ride->id,
                'status'               => $ride->status,
                'driver_arrived'       => (bool) $ride->arrived_at,
                'passenger_boarded'    => (bool) $ride->passenger_boarded_at,
                'driver'               => [
                    'id'            => $ride->driver->id,
                    'name'          => $ride->driver->name,
                    'avatar'        => $ride->driver->avatar,
                    'avg_stars'     => round($ride->driver->ratingsReceived()->avg('stars') ?? 0, 1),
                    'total_ratings' => $ride->driver->ratingsReceived()->count(),
                ],
                'vehicle'              => $vehicle,
                'rate_url'             => $ride->status === 'completed' ? route('rides.rate', $ride) : null,
                'boarded_url'          => route('rides.boarded', $ride),
            ] : null,
        ]);
    }

    public function drive(Ride $ride): View
    {
        abort_if($ride->driver_id !== auth()->id(), 403);
        abort_if(!in_array($ride->status, ['accepted', 'in_progress']), 404);

        return view('rides.drive', [
            'ride'    => $ride->load(['rideRequest', 'passenger', 'vehicle']),
            'mapsKey' => config('services.google.maps_key'),
            'devMode' => config('app.ride_matcher') === 'all',
        ]);
    }

    /** Status do ride para o motorista (polling de embarque) */
    public function statusForDriver(Ride $ride): JsonResponse
    {
        abort_if($ride->driver_id !== auth()->id(), 403);

        return response()->json([
            'status'            => $ride->status,
            'driver_arrived'    => (bool) $ride->arrived_at,
            'passenger_boarded' => (bool) $ride->passenger_boarded_at,
        ]);
    }

    /** Motorista chegou ao ponto de embarque */
    public function arrived(Ride $ride): JsonResponse
    {
        abort_if($ride->driver_id !== auth()->id(), 403);
        abort_if($ride->status !== 'accepted', 422);

        $ride->update(['arrived_at' => now()]);
        DriverArrived::dispatch($ride);

        return response()->json(['arrived' => true]);
    }

    /** Passageiro confirma que embarcou */
    public function boarded(Ride $ride): JsonResponse
    {
        abort_if($ride->passenger_id !== auth()->id(), 403);
        abort_if(!$ride->arrived_at, 422);

        $ride->update(['passenger_boarded_at' => now()]);
        PassengerBoarded::dispatch($ride);

        return response()->json(['boarded' => true]);
    }

    public function start(Ride $ride): JsonResponse
    {
        abort_if($ride->driver_id !== auth()->id(), 403);
        abort_if(!$ride->passenger_boarded_at, 422, 'Aguarde o passageiro confirmar o embarque.');

        $this->rideService->start($ride);

        return response()->json(['status' => 'in_progress']);
    }

    public function finish(Ride $ride): JsonResponse
    {
        abort_if($ride->driver_id !== auth()->id(), 403);
        $this->rideService->complete($ride);

        return response()->json([
            'status'   => 'completed',
            'rate_url' => route('rides.rate', $ride),
        ]);
    }

    public function updateLocation(Ride $ride, Request $request): JsonResponse
    {
        abort_if($ride->driver_id !== auth()->id(), 403);
        abort_if(!in_array($ride->status, ['accepted', 'in_progress']), 422);

        $data = $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        DriverLocationUpdated::dispatch($ride, (float) $data['lat'], (float) $data['lng']);

        return response()->json(['ok' => true]);
    }

    public function cancelRequest(RideRequest $rideRequest): JsonResponse
    {
        abort_if($rideRequest->passenger_id !== auth()->id(), 403);
        abort_if(!in_array($rideRequest->status, ['pending', 'accepted']), 422);

        $rideRequest->update(['status' => 'cancelled']);

        if ($rideRequest->ride && in_array($rideRequest->ride->status, ['pending', 'accepted', 'in_progress'])) {
            $ride = $rideRequest->ride->load('passenger');
            $this->rideService->cancel($ride, 'Cancelado pelo passageiro.');
            RideCancelledByPassenger::dispatch($ride);
        }

        return response()->json(['message' => 'Solicitação cancelada.']);
    }

    public function accept(RideRequest $rideRequest): JsonResponse
    {
        $user = auth()->user();

        if (session('user_mode', $user->role) !== 'driver') {
            abort(403, 'Apenas motoristas podem aceitar caronas.');
        }

        $ride = $this->rideService->accept($rideRequest, $user);

        return response()->json([
            ...$ride->toArray(),
            'drive_url' => route('rides.drive', $ride),
        ]);
    }

    public function reject(RideRequest $rideRequest): JsonResponse
    {
        $rideRequest->update(['status' => 'rejected']);

        return response()->json(['message' => 'Solicitação recusada.']);
    }

    public function cancel(Ride $ride, Request $request): JsonResponse
    {
        $request->validate(['reason' => 'required|string']);

        $this->rideService->cancel($ride, $request->input('reason'));

        RideCancelledByDriver::dispatch($ride);

        return response()->json(['message' => 'Carona cancelada.']);
    }

    public function directions(Request $request): JsonResponse
    {
        $request->validate(['origin' => 'required|string', 'destination' => 'required|string']);

        $origin      = $request->query('origin');      // "lat,lng"
        $destination = $request->query('destination'); // "lat,lng"

        // Tenta Google Directions API
        $key = config('services.google.maps_key');
        if ($key) {
            try {
                $resp = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/directions/json', [
                    'origin'      => $origin,
                    'destination' => $destination,
                    'mode'        => 'driving',
                    'language'    => 'pt-BR',
                    'key'         => $key,
                ]);
                $data = $resp->json();
                if (($data['status'] ?? '') === 'OK' && !empty($data['routes'])) {
                    return response()->json([
                        'path' => $this->decodePolyline($data['routes'][0]['overview_polyline']['points']),
                    ]);
                }
            } catch (\Throwable) { /* timeout ou rede — tenta fallback */ }
        }

        // Fallback: OSRM (roteamento open-source, sem custo, mesmo formato de polyline)
        [$oLat, $oLng] = array_pad(explode(',', $origin), 2, '0');
        [$dLat, $dLng] = array_pad(explode(',', $destination), 2, '0');

        try {
            $osrm = Http::timeout(8)->get(
                "https://router.project-osrm.org/route/v1/driving/{$oLng},{$oLat};{$dLng},{$dLat}",
                ['overview' => 'full', 'geometries' => 'polyline']
            );
            $od = $osrm->json();
            if (($od['code'] ?? '') === 'Ok' && !empty($od['routes'])) {
                return response()->json([
                    'path' => $this->decodePolyline($od['routes'][0]['geometry']),
                ]);
            }
        } catch (\Throwable) { /* OSRM indisponível */ }

        return response()->json(['error' => 'NO_ROUTE'], 404);
    }

    private function decodePolyline(string $encoded): array
    {
        $points = [];
        $index  = 0;
        $lat    = 0;
        $lng    = 0;
        $len    = strlen($encoded);

        while ($index < $len) {
            // decodifica latitude
            $shift = 0; $result = 0;
            do {
                $b       = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift  += 5;
            } while ($b >= 0x20);
            $lat += ($result & 1) ? ~($result >> 1) : ($result >> 1);

            // decodifica longitude
            $shift = 0; $result = 0;
            do {
                $b       = ord($encoded[$index++]) - 63;
                $result |= ($b & 0x1f) << $shift;
                $shift  += 5;
            } while ($b >= 0x20);
            $lng += ($result & 1) ? ~($result >> 1) : ($result >> 1);

            $points[] = ['lat' => $lat / 1e5, 'lng' => $lng / 1e5];
        }

        return $points;
    }
}
