<?php

namespace MarcoRieser\Livewire\Tests;

use Livewire\LivewireServiceProvider;
use MarcoRieser\Livewire\ServiceProvider;
use Spatie\LaravelRay\RayServiceProvider;
use Statamic\Testing\AddonTestCase;

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

    protected function enableSynthesizers($app): void
    {
        $config = (require __DIR__.'/../config/statamic-livewire.php')['synthesizers'];
        $config['enabled'] = true;

        $app['config']->set('statamic-livewire.synthesizers', $config);
    }
}
