<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Class LocaleMiddleware.
 */
class LocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  Closure  $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Locale is enabled and allowed to be changed
        if (config('boilerplate.locale.status') && session()->has('locale')) {
            setAllLocale(session()->get('locale'));
        }

        return $next($request);
    }
}
