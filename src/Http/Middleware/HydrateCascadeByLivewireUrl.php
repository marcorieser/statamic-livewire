<?php

namespace MarcoRieser\Livewire\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Statamic\Facades\Cascade;
use Statamic\Facades\Entry;
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
        Cascade::withRequest(RequestFacade::create(uri: Livewire::originalUrl(), method: Livewire::originalMethod()));
    }

    protected function hydrateContent(): void
    {
        $url = Str::of(Livewire::originalUrl())
            ->after(Site::current()->absoluteUrl())
            ->start('/');

        if (! ($entry = Entry::findByUri($url, Site::current()))) {
            return;
        }

        Cascade::withContent($entry);
    }
}
