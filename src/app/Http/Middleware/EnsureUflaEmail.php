<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUflaEmail
{
    private const ALLOWED_PATTERN = '/^[^@]+@([a-z0-9-]+\.)*ufla\.br$/i';

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !preg_match(self::ALLOWED_PATTERN, $user->email)) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Acesso restrito à comunidade UFLA.']);
        }

        return $next($request);
    }
}
