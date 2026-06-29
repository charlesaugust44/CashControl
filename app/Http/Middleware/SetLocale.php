<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->query('lang') === 'toggle') {
            $current = $request->cookie('cashcontrol-language', 'en');
            $new = $current === 'pt_BR' ? 'en' : 'pt_BR';

            $queryParams = $request->query();
            unset($queryParams['lang']);
            
            $redirectUrl = $request->url();
            if (!empty($queryParams)) {
                $redirectUrl .= '?' . http_build_query($queryParams);
            }

            return redirect($redirectUrl)
                ->withCookie(Cookie::make('cashcontrol-language', $new, 525600, '/', null, false, false, false, 'Lax'));
        }

        $locale = $request->cookie('cashcontrol-language');

        if ($locale && in_array($locale, ['en', 'pt_BR'])) {
            app()->setLocale($locale);
            \Carbon\Carbon::setLocale($locale === 'pt_BR' ? 'pt' : 'en');
        }

        return $next($request);
    }
}
