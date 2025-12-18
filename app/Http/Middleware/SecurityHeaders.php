<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME-sniffing attacks
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Control referrer information
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Strict Transport Security (HTTPS only - uncomment when using HTTPS)
        // $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        // Permissions Policy (formerly Feature Policy)
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content Security Policy - adjust based on your app's needs
        // TODO: Remove 'unsafe-inline' and 'unsafe-eval' for production
        // Use nonces or hashes for inline scripts instead
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net https://unpkg.com",
            "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://cdn.datatables.net https://unpkg.com https://fonts.googleapis.com",
            "font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net",
            "img-src 'self' data: https:",
            "connect-src 'self'",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
            "upgrade-insecure-requests",
        ];

        // Only apply strict CSP in production
        if (!config('app.debug')) {
            $response->headers->set('Content-Security-Policy', implode('; ', $csp));
        } else {
            // Use report-only mode in development
            $response->headers->set('Content-Security-Policy-Report-Only', implode('; ', $csp));
        }

        return $response;
    }
}
