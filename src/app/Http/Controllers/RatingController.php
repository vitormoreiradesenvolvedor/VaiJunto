<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use App\Models\Ride;
use App\Services\PointService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function __construct(private PointService $pointService) {}

    public function create(Ride $ride): View|RedirectResponse
    {
        $user = auth()->user();
        abort_if(!in_array($user->id, [$ride->driver_id, $ride->passenger_id]), 403);
        abort_if($ride->status !== 'completed', 422);

        if (Rating::where('ride_id', $ride->id)->where('rater_id', $user->id)->exists()) {
            return redirect()->route('dashboard')->with('success', 'Você já avaliou esta viagem.');
        }

        $ride->load(['driver', 'passenger', 'rideRequest']);

        $ratee = $user->id === $ride->driver_id ? $ride->passenger : $ride->driver;
        $role  = $user->id === $ride->driver_id ? 'driver' : 'passenger';

        return view('rides.rate', compact('ride', 'ratee', 'role'));
    }

    public function store(Ride $ride, Request $request): RedirectResponse
    {
        $user = auth()->user();
        abort_if(!in_array($user->id, [$ride->driver_id, $ride->passenger_id]), 403);
        abort_if($ride->status !== 'completed', 422);

        $data = $request->validate([
            'stars'   => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $role    = $user->id === $ride->driver_id ? 'driver' : 'passenger';
        $rateeId = $user->id === $ride->driver_id ? $ride->passenger_id : $ride->driver_id;

        $rating = Rating::firstOrCreate(
            ['ride_id' => $ride->id, 'rater_id' => $user->id],
            [
                'ratee_id' => $rateeId,
                'stars'    => $data['stars'],
                'comment'  => $data['comment'] ?? null,
                'role'     => $role,
            ],
        );

        if ($rating->wasRecentlyCreated) {
            $this->pointService->award($user->id, 2, 'avaliação enviada');
            $this->pointService->award($rateeId, 2, 'avaliação recebida');
        }

        return redirect()->route('dashboard')->with('success', 'Avaliação enviada! Obrigado pelo feedback.');
    }
}
