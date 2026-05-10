<?php

namespace App\Http\Controllers;

use App\Models\Ride;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,webp|max:2048',
        ]);

        $user = auth()->user();

        $dataUri = $this->resizeAndEncodeAvatar($request->file('avatar'));
        $user->update(['avatar' => $dataUri]);

        return redirect()->route('profile.show')->with('success', 'Foto atualizada com sucesso!');
    }

    private function resizeAndEncodeAvatar(\Illuminate\Http\UploadedFile $file): string
    {
        $maxSize = 200;
        $mime    = $file->getMimeType();

        $src = match ($mime) {
            'image/png'  => imagecreatefrompng($file->getRealPath()),
            'image/webp' => imagecreatefromwebp($file->getRealPath()),
            default      => imagecreatefromjpeg($file->getRealPath()),
        };

        $origW = imagesx($src);
        $origH = imagesy($src);

        $ratio = min($maxSize / $origW, $maxSize / $origH, 1.0);
        $newW  = (int) round($origW * $ratio);
        $newH  = (int) round($origH * $ratio);

        $dst = imagecreatetruecolor($newW, $newH);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($src);

        ob_start();
        imagejpeg($dst, null, 85);
        imagedestroy($dst);
        $binary = ob_get_clean();

        return 'data:image/jpeg;base64,' . base64_encode($binary);
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
