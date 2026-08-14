<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire;

use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Event;
use Livewire\Component;
use MarcoRieser\Livewire\Hooks\CascadeVariablesAutoloader;
use MarcoRieser\Livewire\Hooks\ComputedPropertiesAutoloader;
use MarcoRieser\Livewire\Hooks\SlotsAutoloader;
use MarcoRieser\Livewire\Http\Middleware\HydrateCascadeByLivewireUrl;
use MarcoRieser\Livewire\Http\Middleware\ResolveCurrentSiteByLivewireUrl;
use MarcoRieser\Livewire\Islands\IslandManager;
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

        // Order matters: later hooks win on name collisions, giving
        // public properties > computed properties > cascade data.
        \Livewire\Livewire::componentHook(CascadeVariablesAutoloader::class);
        \Livewire\Livewire::componentHook(ComputedPropertiesAutoloader::class);
    }

    #[Override]
    public function bootAddon(): void
    {
        $this->bootUpdateRouteMiddleware();
        $this->bootStaticCachingReplacers();
        $this->bootSlotsAutoloader();
        $this->bootIslandFileRecovery();
    }

    protected function bootIslandFileRecovery(): void
    {
        // Livewire regenerates missing island cache files by recompiling the
        // component's Blade view — which cannot restore Antlers island files.
        // Restore them before the island renders instead.
        on('call', function (Component $component, string $method, array $params, mixed $componentContext, mixed $returnEarly, mixed $metadata): void {
            if (is_array($metadata) && isset($metadata['island'])) {
                resolve(IslandManager::class)->ensureIslandFiles($component);
            }
        });
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
        // The middleware is attached to the matched route instance instead of
        // the route registry: cached routes are rebuilt from the cache on
        // every request, so registry mutations would be lost under
        // `route:cache`.
        Event::listen(RouteMatched::class, function (RouteMatched $event): void {
            if (! $event->route->named('*livewire.update')) {
                return;
            }

            $middleware = $this->updateRouteMiddleware();

            // Guard against the raw middleware list — gatherMiddleware()
            // would memoize the list before the addition.
            if (array_intersect($middleware, $event->route->middleware()) !== []) {
                return;
            }

            $event->route->middleware($middleware);
        });
    }
}
