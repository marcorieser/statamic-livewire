<?php

namespace MarcoRieser\Livewire;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Livewire\Livewire;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use MarcoRieser\Livewire\Hooks\TransformSynthesizers;
use MarcoRieser\Livewire\Http\Middleware\ResolveCurrentSiteByLivewireUrl;
use Statamic\Http\Middleware\Localize;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        'MarcoRieser\Livewire\Tags\Livewire',
    ];

    public function register(): void
    {
        parent::register();

        $this->registerSynthesizerTransformations();
    }

    public function bootAddon(): void
    {
        $this->bootLocalization();
        $this->bootReplacers();
        $this->bootSynthesizers();
    }

    protected function bootLocalization(): void
    {
        if (! config()->boolean('statamic-livewire.localization.enabled', false)) {
            return;
        }

        collect($this->app->make(Router::class)->getRoutes()->getRoutes())
            ->filter(fn (Route $route) => $route->named('*livewire.update'))
            ->each(fn (Route $route) => $route->middleware([
                ResolveCurrentSiteByLivewireUrl::class,
                Localize::class,
            ]));
    }

    protected function bootReplacers(): void
    {
        config()->set('statamic.static_caching.replacers', array_merge(
            config()->array('statamic-livewire.replacers', []),
            config()->array('statamic.static_caching.replacers', [])
        ));
    }

    protected function bootSynthesizers(): void
    {
        if (! config('statamic-livewire.synthesizers.enabled', false)) {
            return;
        }

        collect(config()->array('statamic-livewire.synthesizers.classes', []))
            ->filter(fn (string $synthesizer) => is_subclass_of($synthesizer, Synth::class))
            ->each(fn (string $synthesizer) => Livewire::propertySynthesizer($synthesizer));
    }

    protected function registerSynthesizerTransformations(): void
    {
        Livewire::componentHook(TransformSynthesizers::class);
    }
}
