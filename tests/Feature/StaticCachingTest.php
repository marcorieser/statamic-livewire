<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Events\ResponsePrepared;
use Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Livewire;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use MarcoRieser\Livewire\Replacers\AssetsReplacer;
use MarcoRieser\Livewire\Replacers\CachedAssetsReplacer;
use MarcoRieser\Livewire\Replacers\DisableBackButtonCacheReplacer;
use MarcoRieser\Livewire\ServiceProvider;
use MarcoRieser\Livewire\Tests\Fixtures\Counter;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Middleware\Cache;
use Statamic\StaticCaching\NoCache\RegionNotFound;
use Statamic\StaticCaching\Replacer;
use Statamic\StaticCaching\Replacers\CsrfTokenReplacer;
use Statamic\StaticCaching\Replacers\NoCacheReplacer;

beforeEach(function (): void {
    SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
    SupportAutoInjectedAssets::$forceAssetInjection = false;
    SupportScriptsAndAssets::$renderedAssets = [];
    SupportScriptsAndAssets::$nonLivewireAssets = [];
    SupportScriptsAndAssets::$alreadyRunAssetKeys = [];
});

it('serves cached pages with livewire assets and back-button-cache headers', function (): void {
    config([
        'statamic.static_caching.strategy' => 'half',
        'statamic.static_caching.strategies.half' => ['driver' => 'application', 'expiry' => null],
    ]);

    Livewire::component('counter', Counter::class);

    $middleware = resolve(Cache::class);
    $request = Request::create('http://localhost/cached-page');

    $miss = $middleware->handle($request, function () {
        SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

        return new Response(antlers('<html><head></head><body>{{ livewire:counter }}</body></html>'));
    });

    if (! $miss instanceof Response) {
        throw new RuntimeException('Expected a response from the cache middleware.');
    }

    expect($miss->getStatusCode())->toBe(200);

    // The cacher defers its cache write until the response is prepared for
    // sending, which never happens for a directly invoked middleware.
    event(new ResponsePrepared($request, $miss));

    // Reset livewire's render state as a fresh request would have it.
    SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
    resolve(FrontendAssets::class)->hasRenderedStyles = false;
    resolve(FrontendAssets::class)->hasRenderedScripts = false;

    $hit = $middleware->handle($request, function (): never {
        throw new RuntimeException('The page was not served from the static cache.');
    });

    if (! $hit instanceof Response) {
        throw new RuntimeException('Expected a response from the cache middleware.');
    }

    expect((string) $hit->getContent())
        ->toContain('counter: 0')
        ->toContain('livewire')
        ->and((string) $hit->headers->get('Cache-Control'))->toContain('no-store')
        ->and($hit->headers->get('Pragma'))->toBe('no-cache');
});

it('registers the replacers for static caching', function (): void {
    expect(config('statamic.static_caching.replacers'))
        ->toContain(AssetsReplacer::class)
        ->toContain(DisableBackButtonCacheReplacer::class);
});

it('does not duplicate replacers when the addon boots more than once', function (): void {
    // `artisan config:cache` boots a second application instance internally,
    // running the addon's boot logic again against the same config values.
    new ServiceProvider(app())->bootAddon();
    new ServiceProvider(app())->bootAddon();

    /** @var list<class-string> $configured */
    $configured = config('statamic.static_caching.replacers');
    $replacers = collect($configured);

    expect($replacers->filter(fn (string $replacer): bool => $replacer === AssetsReplacer::class))->toHaveCount(1)
        ->and($replacers->filter(fn (string $replacer): bool => $replacer === DisableBackButtonCacheReplacer::class))->toHaveCount(1)
        ->and($replacers->filter(fn (string $replacer): bool => $replacer === CachedAssetsReplacer::class))->toHaveCount(1);
});

it('registers the replacers around statamic\'s own replacers', function (): void {
    config(['statamic.static_caching.replacers' => [
        CachedAssetsReplacer::class,
        CsrfTokenReplacer::class,
        NoCacheReplacer::class,
    ]]);

    new ServiceProvider(app())->bootAddon();

    expect(config('statamic.static_caching.replacers'))->toBe([
        AssetsReplacer::class,
        DisableBackButtonCacheReplacer::class,
        CsrfTokenReplacer::class,
        NoCacheReplacer::class,
        CachedAssetsReplacer::class,
    ]);
});

it('caches the csrf placeholder instead of the token in auto-injected livewire scripts', function (): void {
    useApplicationStaticCache();
    session()->start();

    $request = Request::create('http://localhost/'.uniqid('csrf-'));

    handleThroughStaticCache($request, function (): Response {
        SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

        return new Response('<html><head></head><body><div wire:id="a"></div></body></html>');
    });

    $cached = (string) resolve(Cacher::class)->getCachedPage($request)->toResponse($request)->getContent();

    expect($cached)->toContain('data-csrf="'.CsrfTokenReplacer::REPLACEMENT.'"')
        ->not->toContain(csrf_token());
});

it('caches the fallback render with livewire assets when a nocache region is missing', function (): void {
    useApplicationStaticCache();
    config(['livewire.inject_assets' => false]);

    // Stands in for NoCacheReplacer when the page's nocache region was pruned or expired.
    $noCache = new class implements Replacer
    {
        public bool $regionMissing = false;

        public function prepareResponseToCache(Response $response, Response $initial): void {}

        public function replaceInCachedResponse(Response $response): void
        {
            if ($this->regionMissing) {
                throw new RegionNotFound('region');
            }
        }
    };
    app()->instance(NoCacheReplacer::class, $noCache);

    $request = Request::create('http://localhost/'.uniqid('missing-region-'));
    $page = function (): Response {
        SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

        // The layout loads the assets via {{ livewire:styles }} and {{ livewire:scripts }}.
        return new Response('<html><head>'.FrontendAssets::styles().'</head><body><div wire:id="a"></div>'.FrontendAssets::scripts().'</body></html>');
    };

    handleThroughStaticCache($request, $page);

    $noCache->regionMissing = true;
    $fallback = handleThroughStaticCache($request, $page);
    $noCache->regionMissing = false;

    $hit = handleThroughStaticCache($request, fn (): never => throw new RuntimeException('The page was not served from the static cache.'));

    expect(substr_count((string) $fallback->getContent(), 'data-module-url='))->toBe(1)
        ->and(substr_count((string) $hit->getContent(), 'data-module-url='))->toBe(1)
        ->and(substr_count((string) $hit->getContent(), '<!-- Livewire Styles -->'))->toBe(1);
});

it('does not duplicate livewire assets when a nocache region rerenders a component on a cache hit', function (): void {
    useApplicationStaticCache();

    // Stands in for NoCacheReplacer re-rendering a Livewire component live on a cache hit.
    app()->instance(NoCacheReplacer::class, new class implements Replacer
    {
        public function prepareResponseToCache(Response $response, Response $initial): void {}

        public function replaceInCachedResponse(Response $response): void
        {
            SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;
        }
    });

    $request = Request::create('http://localhost/'.uniqid('nocache-rerender-'));

    handleThroughStaticCache($request, function (): Response {
        SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

        return new Response('<html><head></head><body><div wire:id="a"></div></body></html>');
    });

    $hit = handleThroughStaticCache($request, fn (): never => throw new RuntimeException('The page was not served from the static cache.'));

    expect(substr_count((string) $hit->getContent(), 'data-module-url='))->toBe(1)
        ->and(substr_count((string) $hit->getContent(), '<!-- Livewire Styles -->'))->toBe(1);
});

function useApplicationStaticCache(): void
{
    config([
        'statamic.static_caching.strategy' => 'half',
        'statamic.static_caching.strategies.half' => ['driver' => 'application', 'expiry' => null],
    ]);
}

/**
 * Runs a request through the static cache middleware the way the HTTP
 * kernel would, starting from a fresh Livewire render state.
 *
 * @param  Closure(Request): Response  $next
 */
function handleThroughStaticCache(Request $request, Closure $next): Response
{
    SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
    resolve(FrontendAssets::class)->hasRenderedStyles = false;
    resolve(FrontendAssets::class)->hasRenderedScripts = false;

    $response = resolve(Cache::class)->handle($request, $next);

    if (! $response instanceof Response) {
        throw new RuntimeException('Expected a response from the cache middleware.');
    }

    $response->headers->set('Content-Type', 'text/html');

    // The cacher writes on ResponsePrepared, Livewire injects its own assets on RequestHandled.
    event(new ResponsePrepared($request, $response));
    event(new RequestHandled($request, $response));

    return $response;
}

it('bakes the livewire assets into responses prepared for caching', function (): void {
    config(['statamic.static_caching.strategy' => 'half']);
    SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

    $response = new Response('<html><head></head><body><div>content</div></body></html>');

    (new AssetsReplacer)->prepareResponseToCache($response, $response);

    expect((string) $response->getContent())
        ->toContain('<style')
        ->toContain('livewire')
        ->and(resolve(FrontendAssets::class)->hasRenderedStyles)->toBeFalse()
        ->and(resolve(FrontendAssets::class)->hasRenderedScripts)->toBeFalse();
});

it('bakes custom assets into responses prepared for caching', function (): void {
    config(['statamic.static_caching.strategy' => 'half']);
    SupportScriptsAndAssets::$renderedAssets = ['key' => '<link rel="stylesheet" href="/custom.css">'];

    $response = new Response('<html><head></head><body><div>content</div></body></html>');

    (new AssetsReplacer)->prepareResponseToCache($response, $response);

    expect((string) $response->getContent())->toContain('/custom.css');
});

it('does not touch responses when static caching is disabled', function (): void {
    SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

    $content = '<html><head></head><body><div>content</div></body></html>';
    $response = new Response($content);

    (new AssetsReplacer)->prepareResponseToCache($response, $response);

    expect($response->getContent())->toBe($content);
});

it('skips livewire asset injection when disabled', function (): void {
    config(['statamic.static_caching.strategy' => 'half', 'livewire.inject_assets' => false]);
    SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;
    SupportScriptsAndAssets::$renderedAssets = ['key' => '<link rel="stylesheet" href="/custom.css">'];

    $response = new Response('<html><head></head><body><div>content</div></body></html>');

    (new AssetsReplacer)->prepareResponseToCache($response, $response);

    expect((string) $response->getContent())
        ->toContain('/custom.css')
        ->not->toContain('livewire.min.js');
});

it('leaves responses without livewire untouched when preparing for caching', function (): void {
    config(['statamic.static_caching.strategy' => 'half']);

    $content = '<html><head></head><body><div>content</div></body></html>';
    $response = new Response($content);

    (new AssetsReplacer)->prepareResponseToCache($response, $response);

    expect($response->getContent())->toBe($content);
});

it('ignores empty responses', function (): void {
    config(['statamic.static_caching.strategy' => 'half']);

    $response = new Response('');

    (new AssetsReplacer)->prepareResponseToCache($response, $response);
    (new DisableBackButtonCacheReplacer)->replaceInCachedResponse($response);

    expect($response->getContent())->toBe('')
        ->and($response->headers->has('Pragma'))->toBeFalse();
});

it('marks livewire scripts as already rendered when a cache hit already contains them', function (): void {
    $content = '<html><head></head><body>'
        .'<script src="/livewire/livewire.min.js" data-module-url="/livewire" data-update-uri="/livewire/update"></script>'
        .'</body></html>';

    (new CachedAssetsReplacer)->replaceInCachedResponse(new Response($content));

    expect(resolve(FrontendAssets::class)->hasRenderedScripts)->toBeTrue()
        ->and(FrontendAssets::scripts())->toBeEmpty();
});

it('marks livewire scripts as already rendered when a cache hit contains the script config', function (): void {
    $content = '<html><head></head><body>'
        .'<script data-navigate-once="true">window.livewireScriptConfig = {"csrf":"STATAMIC_CSRF_TOKEN"};</script>'
        .'</body></html>';

    (new CachedAssetsReplacer)->replaceInCachedResponse(new Response($content));

    expect(resolve(FrontendAssets::class)->hasRenderedScripts)->toBeTrue();
});

it('marks livewire styles as already rendered when a cache hit already contains them', function (): void {
    $content = '<html><head><!-- Livewire Styles --><style>[wire\:loading]{}</style></head><body></body></html>';

    (new CachedAssetsReplacer)->replaceInCachedResponse(new Response($content));

    expect(resolve(FrontendAssets::class)->hasRenderedStyles)->toBeTrue()
        ->and(FrontendAssets::styles())->toBeEmpty();
});

it('leaves the render flags untouched on cache hits without baked-in livewire assets', function (): void {
    (new CachedAssetsReplacer)->replaceInCachedResponse(new Response('<html><body><div wire:id="abc">component</div></body></html>'));

    expect(resolve(FrontendAssets::class)->hasRenderedScripts)->toBeFalse()
        ->and(resolve(FrontendAssets::class)->hasRenderedStyles)->toBeFalse();
});

it('ignores empty responses on cache hits', function (): void {
    (new CachedAssetsReplacer)->replaceInCachedResponse(new Response(''));

    expect(resolve(FrontendAssets::class)->hasRenderedScripts)->toBeFalse()
        ->and(resolve(FrontendAssets::class)->hasRenderedStyles)->toBeFalse();
});

it('has no effect on the counterpart replacer phases', function (): void {
    $content = '<html><head><!-- Livewire Styles --></head><body><div wire:id="abc">x</div><script data-module-url="/livewire"></script></body></html>';
    $response = new Response($content);

    (new AssetsReplacer)->replaceInCachedResponse($response);
    (new CachedAssetsReplacer)->prepareResponseToCache($response, $response);
    (new DisableBackButtonCacheReplacer)->prepareResponseToCache($response, $response);

    expect($response->getContent())->toBe($content)
        ->and($response->headers->has('Pragma'))->toBeFalse()
        ->and(resolve(FrontendAssets::class)->hasRenderedScripts)->toBeFalse()
        ->and(resolve(FrontendAssets::class)->hasRenderedStyles)->toBeFalse();
});

it('restores the back-button-cache headers on cached pages with components', function (): void {
    $response = new Response('<html><body><div wire:id="abc">component</div></body></html>');

    (new DisableBackButtonCacheReplacer)->replaceInCachedResponse($response);

    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($response->headers->get('Pragma'))->toBe('no-cache')
        // Each header is set exactly once, replacing existing values.
        ->and($response->headers->all('Cache-Control'))->toHaveCount(1)
        ->and($response->headers->all('Pragma'))->toHaveCount(1);
});

it('leaves cached pages without components cacheable by the browser', function (): void {
    $response = new Response('<html><body><p>static page</p></body></html>');

    (new DisableBackButtonCacheReplacer)->replaceInCachedResponse($response);

    expect((string) $response->headers->get('Cache-Control'))->not->toContain('no-store')
        ->and($response->headers->has('Pragma'))->toBeFalse();
});
