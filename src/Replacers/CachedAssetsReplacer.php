<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Replacers;

use Illuminate\Http\Response;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Statamic\StaticCaching\Replacer;

/**
 * Registered after Statamic's replacers, since it must run after
 * NoCacheReplacer: a missing nocache region makes Statamic re-render the
 * page in the same request, and that render needs untouched asset state.
 */
class CachedAssetsReplacer implements Replacer
{
    public function prepareResponseToCache(Response $response, Response $initial): void
    {
        //
    }

    /**
     * NoCacheReplacer can re-render a `{{ nocache }}` component live on a
     * cache hit, flipping Livewire's render-tracking state even though its
     * assets are already baked into the cached HTML.
     */
    public function replaceInCachedResponse(Response $response): void
    {
        $content = $response->getContent();

        if ($content === false || $content === '') {
            return;
        }

        $assets = resolve(FrontendAssets::class);

        // `{{ livewire:scripts }}` and `{{ livewire:scriptConfig }}` respectively.
        if (str_contains($content, 'data-module-url=') || str_contains($content, 'window.livewireScriptConfig')) {
            $assets->hasRenderedScripts = true;
        }

        if (str_contains($content, '<!-- Livewire Styles -->')) {
            $assets->hasRenderedStyles = true;
        }
    }
}
