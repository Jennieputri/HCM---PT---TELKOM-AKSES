<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class DataGuard
{
    private const MAX_REQUESTS_PER_MINUTE = 120;

    public function handle(Request $request, Closure $next)
    {
        if (! $request->ajax() && ! $request->expectsJson())
        {
            abort(404);
        }

        $user = Auth::user();

        if (! $user)
        {
            return response()->json(['message' => 'Sesi login tidak ditemukan. Silakan login kembali.'], 401);
        }

        $rateLimitKey = 'data-guard:'.$user->nik;

        if (RateLimiter::tooManyAttempts($rateLimitKey, self::MAX_REQUESTS_PER_MINUTE))
        {
            return response()->json(['message' => 'Terlalu banyak permintaan. Coba lagi sebentar.'], 429);
        }

        RateLimiter::hit($rateLimitKey, 60);

        return $next($request);
    }
}
