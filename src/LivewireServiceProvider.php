<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire;

use Illuminate\Support\ServiceProvider;
use MarcoRieser\Livewire\Console\Commands\LivewireCommand;

class LivewireServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/statamic-livewire.php', 'statamic-livewire');

        $this->app->singleton(Livewire::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/statamic-livewire.php');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'statamic-livewire');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'statamic-livewire');

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/statamic-livewire.php' => config_path('statamic-livewire.php'),
        ], ['statamic-livewire', 'statamic-livewire-config']);

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/statamic-livewire'),
        ], ['statamic-livewire', 'statamic-livewire-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/statamic-livewire'),
        ], ['statamic-livewire', 'statamic-livewire-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/statamic-livewire'),
        ], ['statamic-livewire', 'statamic-livewire-assets']);

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], ['statamic-livewire', 'statamic-livewire-migrations']);

        $this->commands([
            LivewireCommand::class,
        ]);
    }
}
