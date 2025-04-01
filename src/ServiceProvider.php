<?php

namespace MarcoRieser\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Statamic\Facades\Site;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        'MarcoRieser\Livewire\Tags\Livewire',
    ];

    public function bootAddon(): void
    {
        $this->bootLocalization();
        $this->bootReplacers();
        $this->bootSyntesizers();
    }

    protected function bootLocalization(): void
    {
        if (! config('statamic-livewire.localization.enabled', false)) {
            return;
        }

        Site::resolveCurrentUrlUsing(fn () => Livewire::originalUrl());

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware(array_merge(
                    Arr::get(Route::getMiddlewareGroups(), 'web', []),
                    [\Statamic\Http\Middleware\Localize::class]
                ));
        });
    }

    protected function bootReplacers(): void
    {
        config()->set('statamic.static_caching.replacers', array_merge(
            config('statamic-livewire.replacers'),
            config('statamic.static_caching.replacers')
        ));
    }

    protected function bootSyntesizers(): void
    {
        if (! config('statamic-livewire.synthesizers.enabled', false)) {
            return;
        }

        $synthesizers = config('statamic-livewire.synthesizers.classes', []);

        foreach ($synthesizers as $synthesizer) {
            Livewire::propertySynthesizer($synthesizer);
        }
    }
}
