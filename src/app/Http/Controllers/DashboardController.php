<?php

namespace App\Http\Controllers;

use App\Models\RideRequest;
use App\Models\Ride;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = auth()->user();

        if ($user->role === 'driver') {
            $pendingRequests = RideRequest::with('passenger')
                ->where('status', 'pending')
                ->where('passenger_id', '!=', $user->id)
                ->orderBy('scheduled_for')
                ->get();

            $myRides = Ride::with(['rideRequest', 'passenger'])
                ->where('driver_id', $user->id)
                ->whereIn('status', ['accepted', 'in_progress'])
                ->orderByDesc('created_at')
                ->get();

            return view('dashboard', compact('pendingRequests', 'myRides'));
        }

        $rideRequests = RideRequest::where('passenger_id', $user->id)
            ->orderByDesc('scheduled_for')
            ->get();

        return view('dashboard', compact('rideRequests'));
    }
}
