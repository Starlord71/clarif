<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the locale chosen through the language switcher on every request.
 *
 * The locale is read from the session and validated against the supported
 * locales. When nothing valid is stored, the configured application default
 * is used. There is no Accept-Language auto-detection: the switch is explicit.
 */
class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');
        $supported = config('clarif.supported_locales', ['en', 'es']);

        if (is_string($locale) && in_array($locale, $supported, true)) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
