<?php

declare(strict_types=1);

use Livewire\Livewire;
use MarcoRieser\Livewire\Tests\Fixtures\BladeComputedCounter;
use MarcoRieser\Livewire\Tests\Fixtures\ComputedCounter;

beforeEach(function (): void {
    Livewire::component('computed-counter', ComputedCounter::class);
    Livewire::component('blade-computed-counter', BladeComputedCounter::class);
});

it('exposes computed properties to antlers component views', function (): void {
    expect(antlers('{{ livewire:computed-counter }}'))->toContain('double: 4');
});

it('does not inject computed properties into blade component views', function (): void {
    expect(antlers('{{ livewire:blade-computed-counter }}'))
        ->toContain('injected: no')
        ->toContain('native: 4');
});

it('recomputes computed properties on updates', function (): void {
    Livewire::test(ComputedCounter::class)
        ->assertSee('double: 4')
        ->set('count', 5)
        ->assertSee('double: 10');
});
