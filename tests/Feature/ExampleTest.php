<?php

declare(strict_types=1);

use MarcoRieser\Livewire\Livewire;

it('resolves the singleton', function () {
    expect(app(Livewire::class))->toBeInstanceOf(Livewire::class);
});

it('returns the same instance from the container', function () {
    expect(app(Livewire::class))->toBe(app(Livewire::class));
});

it('merges the package config', function () {
    expect(config('statamic-livewire.placeholder'))->toBe('default');
});

it('loads the package translations', function () {
    expect(trans('statamic-livewire::messages.placeholder'))->toBe('Livewire placeholder translation.');
});

it('loads the package views', function () {
    expect(view()->exists('statamic-livewire::placeholder'))->toBeTrue();
});

it('registers the artisan command', function () {
    $this->artisan('statamic-livewire:placeholder')
        ->expectsOutputToContain('Livewire placeholder command executed.')
        ->assertSuccessful();
});
