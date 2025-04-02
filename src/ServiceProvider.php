<?php

namespace MarcoRieser\Livewire;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Livewire\Livewire;
use MarcoRieser\Livewire\Http\Middleware\ResolveCurrentSiteByLivewireUrl;
use Statamic\Http\Middleware\Localize;
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

        $router = $this->app->make(Router::class);
        $updateUri = config('statamic-livewire.routes.update', 'livewire/update');

        collect($router->getRoutes()->getRoutes())
            ->filter(fn (Route $route) => $route->uri() === $updateUri)
            ->each(fn (Route $route) => $route->middleware([
                ResolveCurrentSiteByLivewireUrl::class,
                Localize::class,
            ]));
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
