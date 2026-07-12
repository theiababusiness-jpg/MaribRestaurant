<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // 1) من session
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }

        // 2) من ?lang=en
        if ($request->has('lang')) {
            $lang = $request->get('lang');
            if (in_array($lang, ['ar', 'en'])) {
                Session::put('locale', $lang);
                App::setLocale($lang);
            }
        }

        return $next($request);
    }
}
