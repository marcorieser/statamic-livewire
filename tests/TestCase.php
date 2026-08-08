<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests;

use Livewire\LivewireServiceProvider;
use MarcoRieser\Livewire\ServiceProvider;
use Override;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    #[Override]
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
            ...parent::getPackageProviders($app),
        ];
    }
}
