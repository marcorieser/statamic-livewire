<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Route;
use Livewire\Livewire;
use MarcoRieser\Livewire\Attributes\Cascade;
use MarcoRieser\Livewire\Http\Middleware\HydrateCascadeByLivewireUrl;
use MarcoRieser\Livewire\Tests\Fixtures\CascadeComputedViewer;
use MarcoRieser\Livewire\Tests\Fixtures\CascadeViewer;
use MarcoRieser\Livewire\Tests\Fixtures\MissingCascadeKeyViewer;
use MarcoRieser\Livewire\Tests\Fixtures\SelectiveCascadeViewer;
use Statamic\Exceptions\CascadeDataNotFoundException;

beforeEach(function (): void {
    Livewire::component('cascade-viewer', CascadeViewer::class);
    Livewire::component('selective-cascade-viewer', SelectiveCascadeViewer::class);
    Livewire::component('missing-cascade-key-viewer', MissingCascadeKeyViewer::class);
});

it('exposes the cascade to antlers component views', function (): void {
    expect(antlers('{{ livewire:cascade-viewer }}'))->toContain('url: http://localhost');
});

it('exposes selected cascade keys with defaults', function (): void {
    expect(antlers('{{ livewire:selective-cascade-viewer }}'))
        ->toContain('url: http://localhost')
        ->toContain('fallback: fallback-value');
});

it('resolves only the selected cascade keys', function (): void {
    $data = new Cascade(['current_url', 'not_in_cascade' => 'fallback-value'])->getCascadeData();

    expect($data)->toHaveCount(2)
        ->toHaveKeys(['current_url', 'not_in_cascade'])
        ->and($data['not_in_cascade'])->toBe('fallback-value');
});

it('resolves dotted cascade keys', function (): void {
    Statamic\Facades\Cascade::hydrate();
    Statamic\Facades\Cascade::set('seo', ['title' => 'deep']);

    expect(new Cascade(['seo.title'])->getCascadeData())->toBe(['seo.title' => 'deep']);
});

it('throws for a non-string cascade key', function (): void {
    new Cascade([123])->getCascadeData();
})->throws(InvalidArgumentException::class, 'Cascade keys must be strings.');

it('throws for a selected cascade key that does not exist', function (): void {
    antlers('{{ livewire:missing-cascade-key-viewer }}');
})->throws(CascadeDataNotFoundException::class);

it('lets computed properties win over cascade data', function (): void {
    Livewire::component('cascade-computed-viewer', CascadeComputedViewer::class);

    expect(antlers('{{ livewire:cascade-computed-viewer }}'))->toContain('title: from-computed');
});

it('attaches the cascade middleware to the livewire update route', function (): void {
    $route = resolve('router')->getRoutes()->getByName('default-livewire.update');

    if (! $route instanceof Route) {
        $this->fail('The livewire update route is not registered.');
    }

    event(new RouteMatched($route, request()));

    expect($route->gatherMiddleware())->toContain(HydrateCascadeByLivewireUrl::class);
});

it('rehydrates the cascade from the original url on update requests', function (): void {
    $html = antlers('{{ livewire:cascade-viewer }}');

    // In production every request hydrates a fresh cascade; in tests the app
    // persists between the render and the update request, so reset both the
    // container instance and the facade's resolved-instance cache.
    app()->forgetInstance(Statamic\View\Cascade::class);
    Statamic\Facades\Cascade::clearResolvedInstances();

    $response = postLivewireUpdate($this, $html, ['method' => '$refresh', 'params' => []]);

    $response->assertOk();

    // The re-rendered component still sees the original page url, not the
    // update endpoint url.
    expect($response->content())
        ->toContain('url: http:\/\/localhost')
        ->not->toContain('livewire\/update');
});
