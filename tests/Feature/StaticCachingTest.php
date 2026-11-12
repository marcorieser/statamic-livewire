<?php

namespace MarcoRieser\Livewire\Tests\Feature;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Events\ResponsePrepared;
use Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use MarcoRieser\Livewire\Replacers\AssetsReplacer;
use MarcoRieser\Livewire\Replacers\CachedAssetsReplacer;
use MarcoRieser\Livewire\ServiceProvider;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Middleware\Cache;
use Statamic\StaticCaching\NoCache\RegionNotFound;
use Statamic\StaticCaching\Replacer;
use Statamic\StaticCaching\Replacers\CsrfTokenReplacer;
use Statamic\StaticCaching\Replacers\NoCacheReplacer;

class StaticCachingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['statamic.static_caching.strategy' => 'half']);

        SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        SupportAutoInjectedAssets::$forceAssetInjection = false;
        SupportScriptsAndAssets::$renderedAssets = [];
    }

    #[Test]
    public function registers_the_replacer_for_static_caching()
    {
        $this->assertContains(AssetsReplacer::class, config('statamic.static_caching.replacers'));
    }

    #[Test]
    public function does_not_duplicate_the_replacer_when_the_addon_boots_more_than_once()
    {
        // `artisan config:cache` boots a second application instance internally,
        // running the addon's boot logic again against the same config values.
        (new ServiceProvider($this->app))->bootAddon();
        (new ServiceProvider($this->app))->bootAddon();

        $replacers = array_count_values(config('statamic.static_caching.replacers'));

        $this->assertSame(1, $replacers[AssetsReplacer::class]);
        $this->assertSame(1, $replacers[CachedAssetsReplacer::class]);
    }

    #[Test]
    public function registers_the_replacers_around_statamics_own_replacers()
    {
        config(['statamic.static_caching.replacers' => [
            CachedAssetsReplacer::class,
            CsrfTokenReplacer::class,
            NoCacheReplacer::class,
        ]]);

        (new ServiceProvider($this->app))->bootAddon();

        $this->assertSame([
            AssetsReplacer::class,
            CsrfTokenReplacer::class,
            NoCacheReplacer::class,
            CachedAssetsReplacer::class,
        ], config('statamic.static_caching.replacers'));
    }

    #[Test]
    public function caches_the_csrf_placeholder_instead_of_the_token_in_auto_injected_livewire_scripts()
    {
        config(['statamic.static_caching.strategies.half' => ['driver' => 'application', 'expiry' => null]]);
        session()->start();

        $request = Request::create('http://localhost/'.uniqid('csrf-'));

        $this->handleThroughStaticCache($request, function () {
            SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

            return new Response('<html><head></head><body><div wire:id="a"></div></body></html>');
        });

        $cached = (string) app(Cacher::class)->getCachedPage($request)->toResponse($request)->getContent();

        $this->assertStringContainsString('data-csrf="'.CsrfTokenReplacer::REPLACEMENT.'"', $cached);
        $this->assertStringNotContainsString(csrf_token(), $cached);
    }

    #[Test]
    public function bakes_the_livewire_assets_into_responses_prepared_for_caching()
    {
        SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

        $response = new Response('<html><head></head><body><div>content</div></body></html>');

        (new AssetsReplacer)->prepareResponseToCache($response, $response);

        $content = (string) $response->getContent();

        $this->assertStringContainsString('<style', $content);
        $this->assertStringContainsString('livewire', $content);
        $this->assertFalse(app(FrontendAssets::class)->hasRenderedStyles);
        $this->assertFalse(app(FrontendAssets::class)->hasRenderedScripts);
    }

    #[Test]
    public function leaves_responses_without_livewire_untouched_when_preparing_for_caching()
    {
        $content = '<html><head></head><body><div>content</div></body></html>';
        $response = new Response($content);

        (new AssetsReplacer)->prepareResponseToCache($response, $response);

        $this->assertSame($content, $response->getContent());
    }

    #[Test]
    public function ignores_empty_responses_when_preparing_for_caching()
    {
        $response = new Response('');

        (new AssetsReplacer)->prepareResponseToCache($response, $response);

        $this->assertSame('', $response->getContent());
    }

    #[Test]
    public function marks_livewire_scripts_as_already_rendered_when_a_cache_hit_already_contains_them()
    {
        $content = '<html><head></head><body>'
            .'<script src="/livewire/livewire.min.js" data-csrf="STATAMIC_CSRF_TOKEN" data-update-uri="/livewire/update"></script>'
            .'</body></html>';

        (new CachedAssetsReplacer)->replaceInCachedResponse(new Response($content));

        $this->assertTrue(app(FrontendAssets::class)->hasRenderedScripts);
    }

    #[Test]
    public function marks_livewire_scripts_as_already_rendered_when_a_cache_hit_contains_the_script_config()
    {
        $content = '<html><head></head><body>'
            .'<script data-navigate-once="true">window.livewireScriptConfig = {"csrf":"STATAMIC_CSRF_TOKEN"};</script>'
            .'</body></html>';

        (new CachedAssetsReplacer)->replaceInCachedResponse(new Response($content));

        $this->assertTrue(app(FrontendAssets::class)->hasRenderedScripts);
    }

    #[Test]
    public function has_no_effect_on_the_counterpart_replacer_phases()
    {
        $content = '<html><head><!-- Livewire Styles --></head><body><div wire:id="abc">x</div><script data-update-uri="/livewire/update"></script></body></html>';
        $response = new Response($content);

        (new AssetsReplacer)->replaceInCachedResponse($response);
        (new CachedAssetsReplacer)->prepareResponseToCache($response, $response);

        $this->assertSame($content, $response->getContent());
        $this->assertFalse(app(FrontendAssets::class)->hasRenderedScripts);
        $this->assertFalse(app(FrontendAssets::class)->hasRenderedStyles);
    }

    #[Test]
    public function marks_livewire_styles_as_already_rendered_when_a_cache_hit_already_contains_them()
    {
        $content = '<html><head><!-- Livewire Styles --><style>[wire\:loading]{}</style></head><body></body></html>';

        (new CachedAssetsReplacer)->replaceInCachedResponse(new Response($content));

        $this->assertTrue(app(FrontendAssets::class)->hasRenderedStyles);
    }

    #[Test]
    public function leaves_the_render_flags_untouched_on_cache_hits_without_baked_in_livewire_assets()
    {
        (new CachedAssetsReplacer)->replaceInCachedResponse(new Response('<html><body><div wire:id="abc">component</div></body></html>'));

        $this->assertFalse(app(FrontendAssets::class)->hasRenderedScripts);
        $this->assertFalse(app(FrontendAssets::class)->hasRenderedStyles);
    }

    #[Test]
    public function ignores_empty_responses_on_cache_hits()
    {
        (new CachedAssetsReplacer)->replaceInCachedResponse(new Response(''));

        $this->assertFalse(app(FrontendAssets::class)->hasRenderedScripts);
        $this->assertFalse(app(FrontendAssets::class)->hasRenderedStyles);
    }

    #[Test]
    public function caches_the_fallback_render_with_livewire_assets_when_a_nocache_region_is_missing()
    {
        config([
            'statamic.static_caching.strategies.half' => ['driver' => 'application', 'expiry' => null],
            'livewire.inject_assets' => false,
        ]);

        // Stands in for NoCacheReplacer when the page's nocache region was pruned or expired.
        $this->app->instance(NoCacheReplacer::class, new class implements Replacer
        {
            public bool $regionMissing = false;

            public function prepareResponseToCache(Response $responseToBeCached, Response $initialResponse) {}

            public function replaceInCachedResponse(Response $response)
            {
                if ($this->regionMissing) {
                    throw new RegionNotFound('region');
                }
            }
        });

        $request = Request::create('http://localhost/'.uniqid('missing-region-'));
        $page = function () {
            SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

            // The layout loads the assets via {{ livewire:styles }} and {{ livewire:scripts }}.
            return new Response('<html><head>'.FrontendAssets::styles().'</head><body><div wire:id="a"></div>'.FrontendAssets::scripts().'</body></html>');
        };

        $this->handleThroughStaticCache($request, $page);

        app(NoCacheReplacer::class)->regionMissing = true;
        $fallback = $this->handleThroughStaticCache($request, $page);
        app(NoCacheReplacer::class)->regionMissing = false;

        $hit = $this->handleThroughStaticCache($request, fn () => throw new \RuntimeException('The page was not served from the static cache.'));

        $this->assertSame(1, substr_count((string) $fallback->getContent(), 'data-update-uri='));
        $this->assertSame(1, substr_count((string) $hit->getContent(), 'data-update-uri='));
        $this->assertSame(1, substr_count((string) $hit->getContent(), '<!-- Livewire Styles -->'));
    }

    #[Test]
    public function does_not_duplicate_livewire_assets_when_a_nocache_region_rerenders_a_component_on_a_cache_hit()
    {
        config(['statamic.static_caching.strategies.half' => ['driver' => 'application', 'expiry' => null]]);

        // Stands in for NoCacheReplacer re-rendering a Livewire component live on a cache hit.
        $this->app->instance(NoCacheReplacer::class, new class implements Replacer
        {
            public function prepareResponseToCache(Response $responseToBeCached, Response $initialResponse) {}

            public function replaceInCachedResponse(Response $response)
            {
                SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;
            }
        });

        $request = Request::create('http://localhost/'.uniqid('nocache-rerender-'));

        $this->handleThroughStaticCache($request, function () {
            SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

            return new Response('<html><head></head><body><div wire:id="a"></div></body></html>');
        });

        $hit = $this->handleThroughStaticCache($request, fn () => throw new \RuntimeException('The page was not served from the static cache.'));

        $this->assertSame(1, substr_count((string) $hit->getContent(), 'data-update-uri='));
        $this->assertSame(1, substr_count((string) $hit->getContent(), '<!-- Livewire Styles -->'));
    }

    /**
     * Runs a request through the static cache middleware the way the HTTP
     * kernel would, starting from a fresh Livewire render state.
     */
    protected function handleThroughStaticCache(Request $request, \Closure $next): Response
    {
        SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        app(FrontendAssets::class)->hasRenderedStyles = false;
        app(FrontendAssets::class)->hasRenderedScripts = false;

        $response = app(Cache::class)->handle($request, $next);
        $response->headers->set('Content-Type', 'text/html');

        // The cacher writes on ResponsePrepared, Livewire injects its own assets on RequestHandled.
        event(new ResponsePrepared($request, $response));
        event(new RequestHandled($request, $response));

        return $response;
    }
}
