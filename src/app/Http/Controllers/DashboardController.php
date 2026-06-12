<?php

namespace App\Http\Controllers;

use App\Models\FixedRoute;
use App\Models\Ride;
use App\Models\RideRequest;
use App\Models\Trip;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        if (session('user_mode', $user->role) === 'driver') {
            // Viagens que o motorista ofereceu (abertas ou cheias, inclui as últimas 24h antes de partir)
            $myTrips = Trip::where('driver_id', $user->id)
                ->whereIn('status', ['open', 'full'])
                ->where('departs_at', '>', now()->subHours(24))
                ->withCount(['requests as pending_count' => fn ($q) => $q->where('status', 'pending')])
                ->withCount(['requests as accepted_count' => fn ($q) => $q->where('status', 'accepted')])
                ->orderBy('departs_at')
                ->get();

            // Solicitações avulsas pendentes (sem trip_id e sem rota fixa)
            $pendingRequests = RideRequest::with(['passenger' => fn ($q) => $q
                    ->withAvg('ratingsReceived', 'stars')
                    ->withCount('ratingsReceived')])
                ->whereNull('trip_id')
                ->whereNull('fixed_route_id')
                ->where('status', 'pending')
                ->orderBy('scheduled_for')
                ->get();

            $myRides = Ride::with(['rideRequest', 'passenger'])
                ->where('driver_id', $user->id)
                ->whereIn('status', ['accepted', 'in_progress'])
                ->orderByDesc('created_at')
                ->get();

            $driverRides = Ride::with(['rideRequest', 'passenger', 'ratings' => fn ($q) => $q->where('rater_id', $user->id)])
                ->where('driver_id', $user->id)
                ->orderByDesc('created_at')
                ->get();

            $myFixedRoutes = FixedRoute::where('driver_id', $user->id)
                ->whereIn('status', ['active', 'paused'])
                ->withCount(['requests as pending_count' => fn ($q) => $q->where('status', 'pending')])
                ->orderBy('departure_time')
                ->get();

            $pointTransactions = $user->pointTransactions()
                ->orderByDesc('created_at')
                ->limit(15)
                ->get();

            return view('dashboard', compact('myTrips', 'pendingRequests', 'myRides', 'myFixedRoutes', 'pointTransactions', 'driverRides'));
        }

        // Modo passageiro
        // Carona ativa (accepted ou in_progress) — exibida no topo do dashboard
        // Exclui requisições de rota fixa pausada (banner deve sumir quando rota é pausada)
        $activeRequest = RideRequest::with(['ride.driver', 'ride.vehicle', 'fixedRoute'])
            ->where('passenger_id', $user->id)
            ->whereHas('ride', fn ($q) => $q->whereIn('status', ['accepted', 'in_progress']))
            ->where(function ($q) {
                $q->whereNull('fixed_route_id')
                  ->orWhereHas('fixedRoute', fn ($fq) => $fq->where('status', 'active'));
            })
            ->latest()
            ->first();

        $rideRequests = RideRequest::with(['ride', 'ride.ratings' => fn ($q) => $q->where('rater_id', $user->id)])
            ->where('passenger_id', $user->id)
            ->orderByDesc('scheduled_for')
            ->get();

        $availableTrips = Trip::where('status', 'open')
            ->where('departs_at', '>', now())
            ->where('driver_id', '!=', $user->id)
            ->with('driver')
            ->withCount(['requests as accepted_count' => fn ($q) => $q->where('status', 'accepted')])
            ->orderBy('departs_at')
            ->limit(10)
            ->get();

        $availableFixedRoutes = FixedRoute::where('status', 'active')
            ->where('driver_id', '!=', $user->id)
            ->with('driver')
            ->withCount(['requests as accepted_count' => fn ($q) => $q->where('status', 'accepted')])
            ->orderBy('departure_time')
            ->limit(15)
            ->get();

        // Mapa de requests ativas do passageiro (para mostrar "Acompanhar" no lugar de "Solicitar")
        $myPendingReqs = RideRequest::where('passenger_id', $user->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->get(['id', 'fixed_route_id', 'trip_id', 'status']);

        $myFixedRouteReqMap = $myPendingReqs->whereNotNull('fixed_route_id')->keyBy('fixed_route_id');
        $myTripReqMap       = $myPendingReqs->whereNotNull('trip_id')->keyBy('trip_id');

        return view('dashboard', compact(
            'rideRequests', 'availableTrips', 'availableFixedRoutes', 'activeRequest',
            'myFixedRouteReqMap', 'myTripReqMap',
        ));
    }

    /** Polling: ofertas disponíveis para o passageiro (trips + rotas fixas) */
    public function availableOffers(Request $request): JsonResponse
    {
        $user = auth()->user();

        $knownTripIds  = array_map('intval', explode(',', $request->query('trip_ids', '')));
        $knownRouteIds = array_map('intval', explode(',', $request->query('route_ids', '')));
        $knownTripIds  = array_filter($knownTripIds);
        $knownRouteIds = array_filter($knownRouteIds);

        $trips = Trip::where('status', 'open')
            ->where('departs_at', '>', now())
            ->where('driver_id', '!=', $user->id)
            ->when($knownTripIds, fn ($q) => $q->whereNotIn('id', $knownTripIds))
            ->with('driver')
            ->withCount(['requests as accepted_count' => fn ($q) => $q->where('status', 'accepted')])
            ->orderBy('departs_at')
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'id'           => $t->id,
                'origin'       => $t->origin,
                'destination'  => $t->destination,
                'departs_at'   => $t->departs_at->toIso8601String(),
                'seats_total'  => $t->seats_total,
                'seats_left'   => max(0, $t->seats_total - $t->accepted_count),
                'driver'       => ['id' => $t->driver->id, 'name' => $t->driver->name, 'avatar' => $t->driver->avatar],
            ]);

        $routes = FixedRoute::where('status', 'active')
            ->where('driver_id', '!=', $user->id)
            ->when($knownRouteIds, fn ($q) => $q->whereNotIn('id', $knownRouteIds))
            ->with('driver')
            ->withCount(['requests as accepted_count' => fn ($q) => $q->where('status', 'accepted')])
            ->orderBy('departure_time')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'id'              => $r->id,
                'origin'          => $r->origin,
                'destination'     => $r->destination,
                'departure_time'  => \Carbon\Carbon::parse($r->departure_time)->format('H:i'),
                'days_label'      => $r->days_label,
                'available_seats' => $r->available_seats,
                'seats_left'      => max(0, $r->available_seats - $r->accepted_count),
                'driver'          => ['id' => $r->driver->id, 'name' => $r->driver->name, 'avatar' => $r->driver->avatar],
            ]);

        $removedRouteIds = $knownRouteIds
            ? FixedRoute::whereIn('id', $knownRouteIds)->where('status', '!=', 'active')->pluck('id')->toArray()
            : [];

        // Quando o cliente não tem banner ativo, verifica se alguma solicitação foi aceita
        $activeTrackUrl = null;
        if ($request->boolean('check_active')) {
            $activeReq = RideRequest::where('passenger_id', $user->id)
                ->whereHas('ride', fn ($q) => $q->whereIn('status', ['accepted', 'in_progress']))
                ->where(function ($q) {
                    $q->whereNull('fixed_route_id')
                      ->orWhereHas('fixedRoute', fn ($fq) => $fq->where('status', 'active'));
                })
                ->latest()
                ->first();
            if ($activeReq) {
                $activeTrackUrl = route('rides.track', $activeReq);
            }
        }

        return response()->json([
            'trips'            => $trips,
            'routes'           => $routes,
            'removed_route_ids'=> $removedRouteIds,
            'active_track_url' => $activeTrackUrl,
        ]);
    }

    /** Polling: lista atual de solicitações avulsas pendentes para o motorista */
    public function pendingRequests(): JsonResponse
    {
        abort_if(session('user_mode', 'passenger') !== 'driver', 403);

        $requests = RideRequest::with(['passenger' => fn ($q) => $q
                ->withAvg('ratingsReceived', 'stars')
                ->withCount('ratingsReceived')])
            ->whereNull('trip_id')
            ->whereNull('fixed_route_id')
            ->where('status', 'pending')
            ->orderBy('scheduled_for')
            ->get()
            ->map(fn ($r) => [
                'id'           => $r->id,
                'passenger_id' => $r->passenger_id,
                'origin'       => $r->origin,
                'destination'  => $r->destination,
                'scheduled_for'=> $r->scheduled_for->toIso8601String(),
                'seats_needed' => $r->seats_needed,
                'passenger'    => [
                    'name'          => $r->passenger->name,
                    'avg_stars'     => round($r->passenger->ratings_received_avg_stars ?? 0, 1),
                    'total_ratings' => (int) ($r->passenger->ratings_received_count ?? 0),
                ],
            ]);

        $fixedRoutePending = FixedRoute::where('driver_id', auth()->id())
            ->whereIn('status', ['active', 'paused'])
            ->withCount(['requests as pending_count' => fn ($q) => $q->where('status', 'pending')])
            ->get(['id', 'pending_count'])
            ->mapWithKeys(fn ($fr) => [(string) $fr->id => $fr->pending_count]);

        $tripPending = Trip::where('driver_id', auth()->id())
            ->whereIn('status', ['open', 'full'])
            ->withCount(['requests as pending_count' => fn ($q) => $q->where('status', 'pending')])
            ->get(['id', 'pending_count'])
            ->mapWithKeys(fn ($t) => [(string) $t->id => $t->pending_count]);

        return response()->json([
            'requests'            => $requests,
            'fixed_route_pending' => $fixedRoutePending,
            'trip_pending'        => $tripPending,
        ]);
    }
}

