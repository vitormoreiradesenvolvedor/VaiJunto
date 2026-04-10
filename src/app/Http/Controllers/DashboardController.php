<?php

namespace App\Http\Controllers;

use App\Models\FixedRoute;
use App\Models\Ride;
use App\Models\RideRequest;
use App\Models\Trip;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        if (session('user_mode', $user->role) === 'driver') {
            // Viagens que o motorista ofereceu (abertas ou cheias, ainda não partiram)
            $myTrips = Trip::where('driver_id', $user->id)
                ->whereIn('status', ['open', 'full'])
                ->where('departs_at', '>', now())
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
        $activeRequest = RideRequest::with(['ride.driver', 'ride.vehicle'])
            ->where('passenger_id', $user->id)
            ->whereHas('ride', fn ($q) => $q->whereIn('status', ['accepted', 'in_progress']))
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

        return view('dashboard', compact('rideRequests', 'availableTrips', 'availableFixedRoutes', 'activeRequest'));
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

        return response()->json(['requests' => $requests]);
    }
}

