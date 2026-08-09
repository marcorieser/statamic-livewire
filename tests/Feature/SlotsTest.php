<?php

declare(strict_types=1);

use Livewire\Livewire;
use MarcoRieser\Livewire\Tests\Fixtures\Card;

beforeEach(function (): void {
    Livewire::component('card', Card::class);
});

it('passes the tag pair content as the default slot', function (): void {
    $html = antlers('{{ livewire:card }}<p>slotted content</p>{{ /livewire:card }}');

    expect($html)->toContain('<main>')
        ->toContain('<p>slotted content</p>');
});

it('resolves context variables in slot content', function (): void {
    $html = antlers('{{ livewire:card }}<p>{{ greeting }}</p>{{ /livewire:card }}', ['greeting' => 'hello']);

    expect($html)->toContain('<p>hello</p>');
});

it('passes named slots', function (): void {
    $html = antlers(<<<'ANTLERS'
        {{ livewire:card }}
            {{ livewire:slot name="header" }}<h1>title</h1>{{ /livewire:slot }}
            <p>body</p>
        {{ /livewire:card }}
        ANTLERS);

    expect($html)
        ->toContain('<h1>title</h1>')
        ->toContain('<p>body</p>');
});

it('renders without slots when self-closing', function (): void {
    expect(antlers('{{ livewire:card }}'))->toContain('<main>');
});

it('supports conditionally rendering slots', function (): void {
    expect(antlers('{{ livewire:card }}<p>body</p>{{ /livewire:card }}'))
        ->not->toContain('<header>');
});

it('throws when the slot tag is used outside a component pair', function (): void {
    antlers('{{ livewire:slot name="header" }}<h1>title</h1>{{ /livewire:slot }}');
})->throws(RuntimeException::class, 'The {{ livewire:slot }} tag must be used inside a Livewire component tag pair.');

it('throws when a slot has no name', function (): void {
    antlers('{{ livewire:card }}{{ livewire:slot }}<h1>x</h1>{{ /livewire:slot }}{{ /livewire:card }}');
})->throws(InvalidArgumentException::class, 'The {{ livewire:slot }} tag requires a name.');
