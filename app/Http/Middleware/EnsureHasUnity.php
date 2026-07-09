<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasUnity
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && ! $request->user()->unities->count()) {
            return redirect()->route('no-unity');
        }

        return $next($request);
    }
}
