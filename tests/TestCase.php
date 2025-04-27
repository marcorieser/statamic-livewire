<?php

namespace MarcoRieser\Livewire\Tests;

use Livewire\LivewireServiceProvider;
use MarcoRieser\Livewire\ServiceProvider;
use Statamic\Testing\AddonTestCase;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function getPackageProviders($app): array
    {
        return array_merge(
            [LivewireServiceProvider::class],
            parent::getPackageProviders($app)
        );
    }
}
