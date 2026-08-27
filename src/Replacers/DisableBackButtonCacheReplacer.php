<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Replacers;

use Illuminate\Http\Response;
use Livewire\Features\SupportDisablingBackButtonCache\DisableBackButtonCacheMiddleware;
use Statamic\StaticCaching\Replacer;

class DisableBackButtonCacheReplacer implements Replacer
{
    public function prepareResponseToCache(Response $response, Response $initial): void
    {
        //
    }

    /**
     * Livewire stamps no-store headers on every response that rendered a
     * component so browsers don't restore stale snapshots from the
     * back/forward cache. Static caching serves cached pages without those
     * headers, so restore them for cached pages containing components.
     *
     * @see DisableBackButtonCacheMiddleware
     */
    public function replaceInCachedResponse(Response $response): void
    {
        $content = $response->getContent();

        if ($content === false || $content === '') {
            return;
        }

        if (! $this->containsLivewire($content)) {
            return;
        }

        $response->headers->add([
            'Pragma' => 'no-cache',
            'Expires' => 'Fri, 01 Jan 1990 00:00:00 GMT',
            'Cache-Control' => 'no-cache, must-revalidate, no-store, max-age=0, private',
        ]);
    }

    protected function containsLivewire(string $content): bool
    {
        return str_contains($content, 'wire:id')
            || str_contains($content, 'livewire.min.js')
            || str_contains($content, 'livewireScriptConfig');
    }
}
