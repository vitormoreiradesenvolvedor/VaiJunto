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

    private function callbackUrl(): string
    {
        return rtrim(config('app.url'), '/') . '/auth/google/callback';
    }

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirectUrl($this->callbackUrl())->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $socialUser = Socialite::driver('google')->stateless()->redirectUrl($this->callbackUrl())->user();
        } catch (\Throwable) {
            return redirect()->route('login');
        }

        $email = $socialUser->getEmail();

        if (!preg_match(self::UFLA_PATTERN, $email)) {
            return redirect()->route('login')
                ->with('error', 'unauthorized_domain');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'email'  => $email,
                'name'   => $socialUser->getName(),
                'avatar' => $socialUser->getAvatar(),
                'role'   => 'passenger',
            ]);
        } else {
            $updates = ['name' => $socialUser->getName()];
            if (!str_starts_with($user->avatar ?? '', 'data:image/')) {
                $updates['avatar'] = $socialUser->getAvatar();
            }
            $user->update($updates);
        }

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
