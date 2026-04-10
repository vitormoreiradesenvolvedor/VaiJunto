<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function show(?User $user = null): View
    {
        $user = $user ?? auth()->user();

        $ratings = $user->ratingsReceived()
            ->with(['rater', 'ride.rideRequest'])
            ->latest()
            ->get();

        $avgStars      = round($ratings->avg('stars') ?? 0, 1);
        $totalRatings  = $ratings->count();

        $starBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $starBreakdown[$i] = $ratings->where('stars', $i)->count();
        }

        $totalAsDriver    = Ride::where('driver_id', $user->id)->where('status', 'completed')->count();
        $totalAsPassenger = Ride::where('passenger_id', $user->id)->where('status', 'completed')->count();

        // Apenas avaliações com comentário
        $comments = $ratings->filter(fn ($r) => filled($r->comment))->take(20)->values();

        // Para avaliações recentes sem comentário (mostrar atividade)
        $recentRatings = $ratings->take(5);

        $isOwnProfile = auth()->id() === $user->id;

        return view('profile.show', compact(
            'user', 'avgStars', 'totalRatings', 'starBreakdown',
            'totalAsDriver', 'totalAsPassenger', 'comments',
            'recentRatings', 'isOwnProfile'
        ));
    }

    public function reputation(User $user): JsonResponse
    {
        $ratings = $user->ratingsReceived()->with('rater')->latest()->limit(20)->get();

        $starBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $starBreakdown[$i] = $ratings->where('stars', $i)->count();
        }

        return response()->json([
            'id'             => $user->id,
            'name'           => $user->name,
            'avatar'         => $user->avatar,
            'avg_stars'      => round($ratings->avg('stars') ?? 0, 1),
            'total_ratings'  => $ratings->count(),
            'star_breakdown' => $starBreakdown,
            'recent_ratings' => $ratings->map(fn ($r) => [
                'stars'      => $r->stars,
                'comment'    => $r->comment,
                'rater_name' => $r->rater->name,
                'role'       => $r->role,
                'created_at' => $r->created_at->diffForHumans(),
            ]),
        ]);
    }
}
