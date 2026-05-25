<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check for language parameter in query string
        if ($request->has('lang')) {
            $lang = $request->query('lang');
            if (in_array($lang, ['en', 'ar'])) {
                session(['locale' => $lang]);
            }
        }
        
        // Get language from session or default to English
        $locale = session('locale', 'en');
        App::setLocale($locale);
        
        return $next($request);
    }
}
