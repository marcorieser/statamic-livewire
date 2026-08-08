<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests;

use MarcoRieser\Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LivewireServiceProvider::class,
        ];
    }
}
