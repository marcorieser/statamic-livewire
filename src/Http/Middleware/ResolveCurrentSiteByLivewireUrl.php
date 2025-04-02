<?php

namespace MarcoRieser\Livewire\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Statamic\Facades\Site;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentSiteByLivewireUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Site::resolveCurrentUrlUsing(fn () => Livewire::originalUrl());

        return $next($request);
    }
}
