<?php

namespace MarcoRieser\Livewire\Tests;

use Illuminate\Support\Arr;
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

    protected function setConfigValue(string $key, $value): void
    {
        $config = config()->array('statamic-livewire', require __DIR__.'/../config/statamic-livewire.php');

        Arr::set($config, $key, $value);

        config()->set('statamic-livewire', $config);
    }
}
