<?php

declare(strict_types=1);

use Livewire\Livewire;
use MarcoRieser\Livewire\Tests\Fixtures\Counter;

beforeEach(function (): void {
    Livewire::component('counter', Counter::class);
});

it('mounts a component via the wildcard tag', function (): void {
    $html = antlers('{{ livewire:counter }}');

    expect($html)
        ->toContain('wire:id')
        ->toContain('counter: 0');
});

it('passes parameters to the component', function (): void {
    $html = antlers('{{ livewire:counter label="clicks" :count="initial" }}', ['initial' => 5]);

    expect($html)->toContain('clicks: 5');
});

it('mounts a component via the component parameter', function (): void {
    $html = antlers('{{ livewire component="counter" }}');

    expect($html)->toContain('counter: 0');
});

it('mounts a dynamically resolved component', function (): void {
    $html = antlers('{{ livewire:component :name="name" }}', ['name' => 'counter']);

    expect($html)->toContain('counter: 0');
});

it('throws when no component is given', function (string $template): void {
    antlers($template);
})->with([
    'no component parameter' => '{{ livewire }}',
    'no name parameter' => '{{ livewire:component }}',
])->throws(InvalidArgumentException::class, 'The {{ livewire }} tag requires a component name.');

it('mounts a component through the tag aliases', function (): void {
    expect(antlers('{{ lw:counter }}'))->toContain('counter: 0')
        ->and(antlers('{{ wire:counter }}'))->toContain('counter: 0');
});

it('converts parameters to plain values', function (): void {
    $html = antlers('{{ livewire:counter :count="value" }}', ['value' => collect([3])->first()]);

    expect($html)->toContain('counter: 3');
});

it('uses key as the component key instead of passing it as a parameter', function (): void {
    $html = antlers('{{ livewire:counter key="my-key" }}');

    expect($html)
        ->toContain('wire:key="my-key"')
        ->not->toContain('&quot;key&quot;:&quot;my-key&quot;');
});

it('does not pass the component name as a parameter', function (): void {
    $html = antlers('{{ livewire component="counter" }}');

    expect($html)->not->toContain('&quot;component&quot;:&quot;counter&quot;');
});
