<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Replacers;

use Illuminate\Http\Response;
use Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use Statamic\StaticCaching\Cachers\NullCacher;
use Statamic\StaticCaching\Replacer;
use Statamic\StaticCaching\StaticCacheManager;

class AssetsReplacer implements Replacer
{
    /**
     * Bake Livewire's auto-injected assets into the cached response, since
     * asset injection happens after the response was prepared for caching.
     */
    public function prepareResponseToCache(Response $response, Response $initial): void
    {
        $content = $response->getContent();

        if ($content === false || $content === '') {
            return;
        }

        // Don't disturb Livewire's assets injection when caching is off.
        if (resolve(StaticCacheManager::class)->driver() instanceof NullCacher) {
            return;
        }

        $assetsHead = '';
        $assetsBody = '';

        SupportScriptsAndAssets::processNonLivewireAssets();

        foreach (SupportScriptsAndAssets::getAssets() as $asset) {
            $assetsHead .= $asset."\n";
        }

        if ($this->shouldInjectLivewireAssets()) {
            $assetsHead .= FrontendAssets::styles()."\n";
            $assetsBody .= FrontendAssets::scripts()."\n";

            // Ensure Livewire still injects its assets into the initial response.
            resolve(FrontendAssets::class)->hasRenderedStyles = false;
            resolve(FrontendAssets::class)->hasRenderedScripts = false;
        }

        if ($assetsHead === '' && $assetsBody === '') {
            return;
        }

        $response->setContent(
            SupportAutoInjectedAssets::injectAssets($content, $assetsHead, $assetsBody),
        );
    }

    public function replaceInCachedResponse(Response $response): void
    {
        //
    }

    protected function shouldInjectLivewireAssets(): bool
    {
        if (! SupportAutoInjectedAssets::$forceAssetInjection && ! config()->boolean('livewire.inject_assets', true)) {
            return false;
        }

        if (! SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest && ! SupportAutoInjectedAssets::$forceAssetInjection) {
            return false;
        }

        return ! resolve(FrontendAssets::class)->hasRenderedScripts;
    }
}
