<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use MarcoRieser\Livewire\Hooks\CascadeVariablesAutoloader;
use MarcoRieser\Livewire\Hooks\ComputedPropertiesAutoloader;
use MarcoRieser\Livewire\Hooks\SlotsAutoloader;
use MarcoRieser\Livewire\Http\Middleware\HydrateCascadeByLivewireUrl;
use MarcoRieser\Livewire\Http\Middleware\ResolveCurrentSiteByLivewireUrl;
use MarcoRieser\Livewire\Tags\Livewire;
use Override;
use Statamic\Http\Middleware\Localize;
use Statamic\Providers\AddonServiceProvider;

use function Livewire\on;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Livewire::class,
    ];

    #[Override]
    public function register(): void
    {
        parent::register();

        \Livewire\Livewire::componentHook(ComputedPropertiesAutoloader::class);
        \Livewire\Livewire::componentHook(CascadeVariablesAutoloader::class);
    }

    #[Override]
    public function bootAddon(): void
    {
        $this->bootUpdateRouteMiddleware();
        $this->bootStaticCachingReplacers();
        $this->bootSlotsAutoloader();
    }

    /**
     * @return list<class-string>
     */
    public function updateRouteMiddleware(): array
    {
        $middleware = [];

        if (config()->boolean('statamic-livewire.localization', true)) {
            $middleware[] = ResolveCurrentSiteByLivewireUrl::class;
            $middleware[] = Localize::class;
        }

        $middleware[] = HydrateCascadeByLivewireUrl::class;

        return $middleware;
    }

    protected function bootSlotsAutoloader(): void
    {
        // Registered as a plain render listener instead of a component hook,
        // so it runs after Livewire's own slot support and can replace the
        // Blade-only slot proxy for Antlers views.
        on('render', new SlotsAutoloader);
    }

    protected function bootStaticCachingReplacers(): void
    {
        config()->set('statamic.static_caching.replacers', [
            ...config()->array('statamic-livewire.replacers', []),
            ...config()->array('statamic.static_caching.replacers', []),
        ]);
    }

    protected function bootUpdateRouteMiddleware(): void
    {
        $middleware = $this->updateRouteMiddleware();

        collect($this->app->make(Router::class)->getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => $route->named('*livewire.update'))
            ->each(fn (Route $route): Route => $route->middleware($middleware));
    }
}
