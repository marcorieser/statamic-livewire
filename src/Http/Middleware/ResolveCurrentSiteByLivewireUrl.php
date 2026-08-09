<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Statamic\Facades\Site;
use Symfony\Component\HttpFoundation\Response;

class ResolveCurrentSiteByLivewireUrl
{
    /**
     * Resolve the current site from the original page URL instead of the
     * Livewire update endpoint URL.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Site::resolveCurrentUrlUsing(fn (): string => Livewire::originalUrl());

        return $next($request);
    }
}
