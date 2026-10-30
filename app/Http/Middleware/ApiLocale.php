<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ApiLocale
{
    public function handle(Request $request, Closure $next)
    {
        // قراءة اللغة من الهيدر، واللغة الافتراضية هي الإنجليزية
        $locale = $request->header('Accept-Language', 'en');
        
        if (in_array($locale, config('translatable.locales'))) {
            App::setLocale($locale);
        } else {
            App::setLocale(config('app.fallback_locale'));
        }

        return $next($request);
    }
}