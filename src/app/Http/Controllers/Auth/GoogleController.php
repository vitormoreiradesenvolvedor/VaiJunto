<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    private const UFLA_PATTERN = '/^[^@]+@([a-z0-9-]+\.)*ufla\.br$/i';

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver('google')->stateless()->user();
        } catch (\Throwable) {
            return redirect()->route('login');
        }

        $email = $socialUser->getEmail();

        if (!preg_match(self::UFLA_PATTERN, $email)) {
            return redirect()->route('login')
                ->with('error', 'unauthorized_domain');
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name'   => $socialUser->getName(),
                'avatar' => $socialUser->getAvatar(),
                'role'   => 'passenger',
            ]
        );

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
