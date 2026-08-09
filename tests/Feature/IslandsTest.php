<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use Livewire\Livewire;
use MarcoRieser\Livewire\Islands\IslandManager;
use MarcoRieser\Livewire\Tests\Fixtures\IslandCounter;
use MarcoRieser\Livewire\Tests\Fixtures\IslandSlotCard;
use MarcoRieser\Livewire\Tests\Fixtures\LazyPlaceholderCounter;

beforeEach(function (): void {
    Livewire::component('island-counter', IslandCounter::class);

    // Start from a clean island cache so all store() branches run.
    File::deleteDirectory(dirname(IslandCompiler::getCachedPathFromToken('probe')));
});

function mountIslands(string $view = 'island-counter'): string
{
    return antlers('{{ livewire:island-counter view="'.$view.'" }}');
}

it('recovers island cache files on island updates after cleared caches', function (): void {
    $html = mountIslands();

    // Simulate `view:clear` between the page render and the island update.
    File::deleteDirectory(dirname(IslandCompiler::getCachedPathFromToken('probe')));

    $response = postLivewireUpdate($this, $html, [
        'method' => 'increment',
        'params' => [],
        'metadata' => ['island' => ['name' => 'stats', 'mode' => 'morph']],
    ]);

    $response->assertOk();

    expect($response->content())->toContain('inside: 1');
});

it('renders islands with fragment markers on mount', function (): void {
    $html = mountIslands();

    expect($html)
        ->toContain('inside: 0')
        ->toMatch('/\[if FRAGMENT:type=island\|name=stats\|token=antlers-[a-f0-9]+\|mode=morph\]/')
        ->toContain('&quot;islands&quot;:[{&quot;name&quot;:&quot;stats&quot;');
});

it('rerenders island content on island-scoped updates', function (): void {
    $response = postLivewireUpdate($this, mountIslands(), [
        'method' => 'increment',
        'params' => [],
        'metadata' => ['island' => ['name' => 'stats', 'mode' => 'morph']],
    ]);

    $response->assertOk();

    expect($response->content())
        ->toContain('islandFragments')
        ->toContain('inside: 1');
});

it('skips islands on full component updates', function (): void {
    $response = postLivewireUpdate($this, mountIslands(), ['method' => 'increment', 'params' => []]);

    $response->assertOk();

    // The component re-renders with the new count, but the island region is
    // marked as skipped so its DOM is left untouched.
    expect($response->content())
        ->toContain('outside: 1')
        ->toContain('mode=skip')
        ->not->toContain('inside: 1');
});

it('renders a placeholder for lazy islands and loads them on trigger', function (): void {
    $html = mountIslands('island-counter-lazy');

    expect($html)
        ->toContain('loading…')
        ->toContain('wire:intersect.once="__lazyLoadIsland"')
        ->not->toContain('inside: 0');

    $response = postLivewireUpdate($this, $html, [
        'method' => '__lazyLoadIsland',
        'params' => [],
        'metadata' => ['island' => ['name' => 'stats', 'mode' => 'morph']],
    ]);

    $response->assertOk();

    expect($response->content())
        ->toContain('inside: 0')
        ->not->toContain('loading…');
});

it('throws when the placeholder tag is used outside an island', function (): void {
    file_put_contents(__DIR__.'/../Fixtures/views/island-counter-stray-placeholder.antlers.html', '<div>{{ livewire:placeholder }}x{{ /livewire:placeholder }}</div>');

    try {
        mountIslands('island-counter-stray-placeholder');
    } finally {
        unlink(__DIR__.'/../Fixtures/views/island-counter-stray-placeholder.antlers.html');
    }
})->throws(RuntimeException::class, 'The {{ livewire:placeholder }} tag must be used inside a {{ livewire:island }} tag pair.');

it('mounts lazy components with their placeholder', function (): void {
    Livewire::component('lazy-placeholder-counter', LazyPlaceholderCounter::class);

    $html = antlers('{{ livewire:lazy-placeholder-counter lazy="true" }}');

    expect($html)
        ->toContain('waiting…')
        ->not->toContain('component content');
});

it('mounts deferred components with their placeholder', function (string $param): void {
    Livewire::component('lazy-placeholder-counter', LazyPlaceholderCounter::class);

    $html = antlers('{{ livewire:lazy-placeholder-counter '.$param.' }}');

    expect($html)
        ->toContain('waiting…')
        ->not->toContain('component content');
})->with([
    'defer' => 'defer="true"',
    'legacy on-load' => 'lazy="on-load"',
]);

it('renders an empty placeholder for deferred islands without a placeholder branch', function (): void {
    $html = mountIslands('island-counter-defer');

    expect($html)
        ->toContain('wire:init="__lazyLoadIsland"')
        ->not->toContain('inside: 0');
});

it('rerenders always islands on full component updates', function (): void {
    $response = postLivewireUpdate($this, mountIslands('island-counter-always'), ['method' => 'increment', 'params' => []]);

    $response->assertOk();

    expect($response->content())
        ->toContain('outside: 1')
        ->toContain('inside: 1')
        ->not->toContain('mode=skip');
});

it('skips the initial render of skip islands', function (): void {
    $html = mountIslands('island-counter-skip');

    expect($html)
        ->toMatch('/\[if FRAGMENT:type=island\|name=stats\|token=antlers-[a-f0-9]+\|mode=morph\]/')
        ->not->toContain('inside: 0');
});

it('keeps placeholders with their island and supports tag aliases', function (): void {
    $html = mountIslands('island-counter-nested-lazy');

    // The outer island renders its content (its alias placeholder stripped),
    // the inner lazy island renders its own placeholder.
    expect($html)
        ->toContain('outer island')
        ->toContain('inner loading')
        ->not->toContain('outer loading')
        ->not->toContain('inner island</span>');
});

it('merges runtime island data into the island scope', function (): void {
    $response = postLivewireUpdate($this, mountIslands(), ['method' => 'flash', 'params' => []]);

    $response->assertOk();

    expect($response->content())->toContain('flash: boom');
});

it('renders slots inside islands', function (): void {
    Livewire::component('island-slot-card', IslandSlotCard::class);

    $html = antlers('{{ livewire:island-slot-card }}<p>slotted</p>{{ /livewire:island-slot-card }}');

    expect($html)->toContain('<p>slotted</p>');

    $response = postLivewireUpdate($this, $html, [
        'method' => '$refresh',
        'params' => [],
        'metadata' => ['island' => ['name' => 'body', 'mode' => 'morph']],
    ]);

    $response->assertOk();

    // Slot content lives in the client's DOM: island updates emit a skip
    // fragment for it, so morphing leaves the slotted markup untouched.
    expect($response->content())->toContain('name=default|type=slot')
        ->toContain('mode=skip');
});

it('renders nested islands', function (): void {
    $html = mountIslands('island-counter-nested');

    expect($html)
        ->toContain('outer island')
        ->toContain('inner island')
        ->toContain('&quot;name&quot;:&quot;outer&quot;')
        ->toContain('&quot;name&quot;:&quot;inner&quot;');
});

it('persists captured island scope across island updates', function (): void {
    $html = mountIslands('island-counter-with');

    expect($html)->toContain('live: 0 captured: 0');

    $response = postLivewireUpdate($this, $html, [
        'method' => 'increment',
        'params' => [],
        'metadata' => ['island' => ['name' => 'stats', 'mode' => 'morph']],
    ]);

    $response->assertOk();

    // The live property changed while the captured value stays as it was
    // when the island was defined.
    expect($response->content())->toContain('live: 1 captured: 0');
});

it('supports islands in loops via dynamic names and captured scope', function (): void {
    $html = mountIslands('island-counter-loop');

    expect($html)
        ->toContain('island-1')
        ->toContain('island-2')
        ->toContain('&quot;name&quot;:&quot;item-1&quot;')
        ->toContain('&quot;name&quot;:&quot;item-2&quot;');

    $response = postLivewireUpdate($this, $html, [
        'method' => '$refresh',
        'params' => [],
        'metadata' => ['island' => ['name' => 'item-2', 'mode' => 'morph']],
    ]);

    $response->assertOk();

    expect($response->content())
        ->toContain('island-2')
        ->not->toContain('island-1');
});

it('throws when the island tag is used outside a component view', function (): void {
    antlers('{{ livewire:island name="stats" }}x{{ /livewire:island }}');
})->throws(RuntimeException::class, 'The {{ livewire:island }} tag must be used inside a Livewire component view.');

it('throws when an island has no name', function (): void {
    file_put_contents(__DIR__.'/../Fixtures/views/island-counter-nameless.antlers.html', '<div>{{ livewire:island }}x{{ /livewire:island }}</div>');

    try {
        mountIslands('island-counter-nameless');
    } finally {
        unlink(__DIR__.'/../Fixtures/views/island-counter-nameless.antlers.html');
    }
})->throws(InvalidArgumentException::class, 'The {{ livewire:island }} tag requires a name.');

it('renders islands without persisted scope gracefully', function (): void {
    $manager = resolve(IslandManager::class);
    $manager->store('antlers-test-scope', '<span>static</span>');

    $componentWithoutIslands = new IslandCounter;

    expect($manager->render('antlers-test-scope', []))->toContain('static')
        ->and($manager->render('antlers-test-scope', ['__livewire' => $componentWithoutIslands]))->toContain('static');
});

it('throws when rendering an island whose source is missing', function (): void {
    resolve(IslandManager::class)->render('antlers-nonexistent', []);
})->throws(RuntimeException::class);
