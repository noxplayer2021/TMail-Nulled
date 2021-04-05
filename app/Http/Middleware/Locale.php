<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Locale {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next) {
        app()->setLocale(session('locale', config('app.settings.language', config('app.locale', 'en'))));
        return $next($request);
    }
}
