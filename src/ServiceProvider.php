<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire;

use MarcoRieser\Livewire\Tags\Livewire;
use Override;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Livewire::class,
    ];

    #[Override]
    public function bootAddon(): void
    {
        //
    }
}
