<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $scriptNonce = Str::random(32);

        view()->share('cspNonce', $scriptNonce);

        $response = $next($request);

        $headers = [
            'Content-Security-Policy'      => $this->contentSecurityPolicy($scriptNonce),
            'X-Frame-Options'              => 'DENY',
            'X-Content-Type-Options'       => 'nosniff',
            'Referrer-Policy'              => 'strict-origin-when-cross-origin',
            'Cross-Origin-Opener-Policy'   => 'same-origin',
            'Cross-Origin-Resource-Policy' => 'same-origin',
            'X-Robots-Tag'                 => 'noindex, nofollow, noai',
        ];

        if ($request->secure())
        {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $headerName => $headerValue)
        {
            if (! $response->headers->has($headerName))
            {
                $response->headers->set($headerName, $headerValue);
            }
        }

        return $response;
    }

    private function contentSecurityPolicy(string $scriptNonce)
    {
        $assetHosts = config('security.csp_asset_hosts');
        $imageHosts = config('security.csp_image_hosts');

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'nonce-{$scriptNonce}' {$assetHosts}",
            "style-src 'self' 'unsafe-inline' {$assetHosts}",
            "font-src 'self' data: {$assetHosts}",
            "img-src 'self' data: blob: {$assetHosts} {$imageHosts}",
            "connect-src 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
        ]);
    }
}
