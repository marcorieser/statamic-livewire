<?php

declare(strict_types=1);

use Livewire\LivewireServiceProvider;
use MarcoRieser\Livewire\ServiceProvider;
use Statamic\Providers\StatamicServiceProvider;

it('registers the addon service provider', function (): void {
    expect($this->app->getProviders(ServiceProvider::class))->toHaveCount(1);
});

it('boots livewire and statamic', function (): void {
    expect($this->app->getProviders(LivewireServiceProvider::class))->toHaveCount(1)
        ->and($this->app->getProviders(StatamicServiceProvider::class))->toHaveCount(1);
});

it('merges the addon config', function (): void {
    expect(config()->has('statamic-livewire'))->toBeTrue();
});
