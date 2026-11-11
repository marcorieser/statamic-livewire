<?php

namespace MarcoRieser\Livewire\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Livewire\Livewire;
use Statamic\Facades\Cascade;
use Statamic\Facades\Data;
use Statamic\Facades\Site;
use Symfony\Component\HttpFoundation\Response;

class HydrateCascadeByLivewireUrl
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->hydrateSite();
        $this->hydrateRequest();
        $this->hydrateContent();

        return $next($request);
    }

    protected function hydrateSite(): void
    {
        Cascade::withSite(Site::current());
    }

    protected function hydrateRequest(): void
    {
        // A component dehydrated while a livewire/update request was failing (e.g. an error
        // page rendered for an expired session) carries a null memo path and method.
        Cascade::withRequest(RequestFacade::create(uri: Livewire::originalUrl(), method: Livewire::originalMethod() ?? 'GET'));
    }

    protected function hydrateContent(): void
    {
        Cascade::withContent(fn () => Data::findByRequestUrl(Livewire::originalUrl()) ?? collect());
    }
}
