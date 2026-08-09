<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Events\ResponsePrepared;
use Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Livewire;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use MarcoRieser\Livewire\Replacers\AssetsReplacer;
use MarcoRieser\Livewire\Replacers\DisableBackButtonCacheReplacer;
use MarcoRieser\Livewire\Tests\Fixtures\Counter;
use Statamic\StaticCaching\Middleware\Cache;

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

it('has no effect on the counterpart replacer phases', function (): void {
    $content = '<html><body><div wire:id="abc">x</div></body></html>';
    $response = new Response($content);

    (new AssetsReplacer)->replaceInCachedResponse($response);
    (new DisableBackButtonCacheReplacer)->prepareResponseToCache($response, $response);

    expect($response->getContent())->toBe($content)
        ->and($response->headers->has('Pragma'))->toBeFalse();
});

it('restores the back-button-cache headers on cached pages with components', function (): void {
    $response = new Response('<html><body><div wire:id="abc">component</div></body></html>');

    (new DisableBackButtonCacheReplacer)->replaceInCachedResponse($response);

    expect($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($response->headers->get('Pragma'))->toBe('no-cache');
});

it('leaves cached pages without components cacheable by the browser', function (): void {
    $response = new Response('<html><body><p>static page</p></body></html>');

    (new DisableBackButtonCacheReplacer)->replaceInCachedResponse($response);

    expect((string) $response->headers->get('Cache-Control'))->not->toContain('no-store')
        ->and($response->headers->has('Pragma'))->toBeFalse();
});
