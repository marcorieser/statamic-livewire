<?php

declare(strict_types=1);

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
     * Rebuild the Statamic cascade for Livewire update requests as if the
     * original page URL was requested, so cascade data stays consistent
     * across component updates.
     *
     * @param  Closure(Request): Response  $next
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
        // Null content is safe (statamic/cms#14502) and mirrors the initial
        // render of URLs without Statamic content: no `page` key is set.
        Cascade::withContent(fn () => Data::findByRequestUrl(Livewire::originalUrl()));
    }
}
