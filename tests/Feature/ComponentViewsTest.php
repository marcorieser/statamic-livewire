<?php

declare(strict_types=1);

use Livewire\Livewire;
use MarcoRieser\Livewire\Tests\Fixtures\AntlersCounter;

beforeEach(function (): void {
    Livewire::component('antlers-counter', AntlersCounter::class);
    Livewire::addLocation(__DIR__.'/../Fixtures/components');
});

it('renders a class component with an antlers view', function (): void {
    $html = antlers('{{ livewire:antlers-counter }}');

    expect($html)
        ->toContain('wire:id')
        ->toContain('antlers count: 0');
});

it('handles updates on a component with an antlers view', function (): void {
    Livewire::test(AntlersCounter::class)
        ->assertSee('antlers count: 0')
        ->call('increment')
        ->assertSee('antlers count: 1');
});

it('mounts a single-file component', function (): void {
    expect(antlers('{{ livewire:sfc-greeting }}'))->toContain('sfc hello world');
});

it('mounts a multi-file component', function (): void {
    expect(antlers('{{ livewire:mfc-badge }}'))->toContain('mfc badge: new');
});
