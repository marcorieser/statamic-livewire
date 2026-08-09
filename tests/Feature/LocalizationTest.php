<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Request;
use Livewire\Livewire;
use MarcoRieser\Livewire\Http\Middleware\HydrateCascadeByLivewireUrl;
use MarcoRieser\Livewire\Http\Middleware\ResolveCurrentSiteByLivewireUrl;
use MarcoRieser\Livewire\Tests\Fixtures\CascadeViewer;
use Statamic\Facades\Site;
use Statamic\Http\Middleware\Localize;
use Statamic\View\Cascade;

beforeEach(function (): void {
    Livewire::component('cascade-viewer', CascadeViewer::class);
});

it('resolves the site before hydrating the cascade on update routes', function (): void {
    $route = resolve('router')->getRoutes()->getByName('default-livewire.update');

    $middleware = collect($route?->gatherMiddleware() ?? [])
        ->filter(fn (mixed $middleware): bool => in_array($middleware, addonProvider()->updateRouteMiddleware(), true))
        ->values()
        ->all();

    expect($middleware)->toBe([
        ResolveCurrentSiteByLivewireUrl::class,
        Localize::class,
        HydrateCascadeByLivewireUrl::class,
    ]);
});

it('skips the localization middleware when disabled', function (): void {
    config(['statamic-livewire.localization' => false]);

    expect(addonProvider()->updateRouteMiddleware())->toBe([
        HydrateCascadeByLivewireUrl::class,
    ]);
});

it('resolves the current site from the original url on update requests', function (): void {
    Site::setSites([
        'en' => ['name' => 'English', 'url' => 'http://localhost/', 'locale' => 'en_US'],
        'de' => ['name' => 'German', 'url' => 'http://localhost/de/', 'locale' => 'de_DE'],
    ]);

    // Render the component as if the page at /de/page was requested, so the
    // snapshot records that path as its origin.
    app()->instance('request', Request::create('http://localhost/de/page'));

    $html = antlers('{{ livewire:cascade-viewer }}');

    preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);
    $snapshot = html_entity_decode($matches[1] ?? '', ENT_QUOTES);

    // In production every request hydrates a fresh cascade; in tests the app
    // persists between the render and the update request, so reset both the
    // container instance and the facade's resolved-instance cache.
    app()->forgetInstance(Cascade::class);
    Statamic\Facades\Cascade::clearResolvedInstances();

    $response = $this
        ->withoutMiddleware(PreventRequestForgery::class)
        ->postJson(Livewire::getUpdateUri(), [
            'components' => [
                [
                    'snapshot' => $snapshot,
                    'updates' => [],
                    'calls' => [
                        ['method' => '$refresh', 'params' => []],
                    ],
                ],
            ],
        ], ['X-Livewire' => '1']);

    $response->assertOk();

    expect($response->content())
        ->toContain('site: de')
        ->toContain('url: http:\/\/localhost\/de\/page');
});
