<?php

namespace MarcoRieser\Livewire\Tests;

use Livewire\LivewireServiceProvider;
use MarcoRieser\Livewire\ServiceProvider;
use Spatie\LaravelRay\RayServiceProvider;
use Statamic\Testing\AddonTestCase;

use function Orchestra\Testbench\package_path;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function getPackageProviders($app): array
    {
        return array_merge(
            [
                LivewireServiceProvider::class,
                RayServiceProvider::class,
            ],
            parent::getPackageProviders($app)
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        view()->addLocation(package_path().'/tests/__fixtures__/views');
    }
}
