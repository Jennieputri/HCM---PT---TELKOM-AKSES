<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureHcmRole
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::user()->isHcm())
        {
            Log::channel('security')->warning('Non-HCM account blocked from HCM area.', [
                'nik'  => Auth::user()->nik,
                'path' => $request->path(),
                'ip'   => $request->ip(),
            ]);

            abort(403, 'Halaman ini khusus untuk role HCM.');
        }

        return $next($request);
    }
}
