<?php

namespace MarcoRieser\Livewire;

use Livewire\Livewire;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $publishAfterInstall = false;

    protected $tags = [
        'MarcoRieser\Livewire\Tags\Livewire',
    ];

    public function bootAddon(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/statamic-livewire.php', 'statamic-livewire');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/statamic-livewire.php' => config_path('statamic-livewire.php'),
            ], 'statamic-livewire');
        }

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
