<?php

namespace App\Http\Middleware;

use App\Support\UnityContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetUnityContext
{
    public function __construct(protected UnityContext $unityContext)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            $unity = $request->user()->unity();
            if ($unity) {
                $this->unityContext->set($unity->id);
            }
        }

        return $next($request);
    }
}
