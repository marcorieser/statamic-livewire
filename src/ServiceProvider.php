<?php

namespace MarcoRieser\Livewire;

use Livewire\Livewire;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        'MarcoRieser\Livewire\Tags\Livewire',
    ];

    public function bootAddon(): void
    {
        $this->bootReplacers();
        $this->bootSyntesizers();
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
