<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Livewire\Livewire;
use MarcoRieser\Livewire\Attributes\Cascade;
use MarcoRieser\Livewire\Http\Middleware\HydrateCascadeByLivewireUrl;
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

it('throws for a non-string cascade key', function (): void {
    new Cascade([123])->getCascadeData();
})->throws(InvalidArgumentException::class, 'Cascade keys must be strings.');

it('throws for a selected cascade key that does not exist', function (): void {
    antlers('{{ livewire:missing-cascade-key-viewer }}');
})->throws(CascadeDataNotFoundException::class);

it('attaches the cascade middleware to the livewire update route', function (): void {
    $route = resolve('router')->getRoutes()->getByName('default-livewire.update');

    expect($route)->not->toBeNull()
        ->and($route?->gatherMiddleware())->toContain(HydrateCascadeByLivewireUrl::class);
});

it('rehydrates the cascade from the original url on update requests', function (): void {
    $html = antlers('{{ livewire:cascade-viewer }}');

    preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);
    $snapshot = html_entity_decode($matches[1] ?? '', ENT_QUOTES);

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

    // The re-rendered component still sees the original page url, not the
    // update endpoint url.
    expect($response->content())
        ->toContain('url: http:\/\/localhost')
        ->not->toContain('livewire\/update');
});
