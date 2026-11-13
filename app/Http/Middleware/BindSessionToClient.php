<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BindSessionToClient
{
    private const FINGERPRINT_KEY = 'client_fingerprint';

    private const AUTHENTICATED_AT_KEY = 'authenticated_at';

    public static function bind(Request $request)
    {
        $request->session()->put([
            self::FINGERPRINT_KEY      => self::fingerprint($request),
            self::AUTHENTICATED_AT_KEY => now()->timestamp,
        ]);
    }

    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check())
        {
            return $next($request);
        }

        $session = $request->session();

        if (! $session->has(self::FINGERPRINT_KEY))
        {
            self::bind($request);

            return $next($request);
        }

        $isSameClient    = hash_equals($session->get(self::FINGERPRINT_KEY), self::fingerprint($request));
        $sessionAgeLimit = (int) $session->get(self::AUTHENTICATED_AT_KEY) + config('security.session_absolute_minutes') * 60;

        if ($isSameClient && now()->timestamp < $sessionAgeLimit)
        {
            return $next($request);
        }

        if (! $isSameClient)
        {
            Log::channel('security')->warning('session.fingerprint_mismatch', [
                'username' => Auth::user()->nik,
                'ip'       => $request->ip(),
            ]);
        }

        Auth::logout();
        $session->invalidate();
        $session->regenerateToken();

        if ($request->expectsJson())
        {
            return response()->json(['message' => 'Sesi berakhir. Silakan login kembali.'], 401);
        }

        return redirect()->route('login')->withErrors(['login' => 'Sesi berakhir. Silakan login kembali.']);
    }

    private static function fingerprint(Request $request)
    {
        $clientParts = [(string) $request->userAgent()];

        if (config('security.session_bind_ip'))
        {
            $clientParts[] = (string) $request->ip();
        }

        return hash('sha256', implode('|', $clientParts));
    }
}
