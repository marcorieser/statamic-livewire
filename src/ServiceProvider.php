<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire;

use Override;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    #[Override]
    public function bootAddon(): void
    {
        //
    }
}
