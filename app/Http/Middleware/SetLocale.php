<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // 1. Check explicit Accept-Language header for API requests
        if ($request->is('api/*') || $request->expectsJson()) {
            $header = $request->header('Accept-Language');
            if ($header && in_array(strtolower(trim($header)), ['en', 'ar'])) {
                $locale = strtolower(trim($header));
            }
        }

        // 2. If authenticated user, use the User model preferred locale method
        if (! $locale && $request->user() && method_exists($request->user(), 'getPreferredLocale')) {
            $locale = $request->user()->getPreferredLocale();
        }

        // 3. Web session locale (e.g., guest language switch)
        if (! $locale && $request->hasSession() && $request->session()->has('locale')) {
            $sessionLocale = $request->session()->get('locale');
            if (in_array($sessionLocale, ['en', 'ar'])) {
                $locale = $sessionLocale;
            }
        }

        // 4. Fallback to default from settings table
        if (! $locale) {
            try {
                $default = Setting::where('name', 'language')->value('default');
                if ($default && in_array(strtolower(trim((string) $default)), ['en', 'ar'])) {
                    $locale = strtolower(trim((string) $default));
                }
            } catch (\Throwable) {
                // Table might not exist yet during migration
            }
        }

        // 5. Final fallback
        if (! in_array($locale, ['en', 'ar'])) {
            $locale = config('app.fallback_locale', 'en');
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
