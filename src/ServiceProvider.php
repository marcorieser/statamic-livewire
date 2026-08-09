<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use MarcoRieser\Livewire\Hooks\CascadeVariablesAutoloader;
use MarcoRieser\Livewire\Hooks\ComputedPropertiesAutoloader;
use MarcoRieser\Livewire\Http\Middleware\HydrateCascadeByLivewireUrl;
use MarcoRieser\Livewire\Tags\Livewire;
use Override;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        Livewire::class,
    ];

    /**
     * Middleware for the Livewire update route, run in order: the current
     * site has to be resolved before the cascade is hydrated with it.
     *
     * @var list<class-string>
     */
    protected array $updateRouteMiddleware = [
        HydrateCascadeByLivewireUrl::class,
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
    }

    protected function bootUpdateRouteMiddleware(): void
    {
        collect($this->app->make(Router::class)->getRoutes()->getRoutes())
            ->filter(fn (Route $route): bool => $route->named('*livewire.update'))
            ->each(fn (Route $route): Route => $route->middleware($this->updateRouteMiddleware));
    }
}
