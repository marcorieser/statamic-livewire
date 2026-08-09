<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire;

use MarcoRieser\Livewire\Hooks\ComputedPropertiesAutoloader;
use MarcoRieser\Livewire\Tags\Livewire;
use Override;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Livewire::class,
    ];

    #[Override]
    public function register(): void
    {
        parent::register();

        \Livewire\Livewire::componentHook(ComputedPropertiesAutoloader::class);
    }

    #[Override]
    public function bootAddon(): void
    {
        //
    }
}
