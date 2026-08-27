<?php

declare(strict_types=1);

namespace Workbench\App\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use MarcoRieser\Livewire\ServiceProvider as AddonServiceProvider;
use Statamic\Addons\Manifest;
use Statamic\Http\Middleware\CheckComposerJsonScripts;
use Statamic\Sites\Sites as StatamicSites;
use Statamic\Version;
use Workbench\App\Statamic\Sites;

use function Orchestra\Testbench\package_path;
use function Orchestra\Testbench\workbench_path;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Sites are the one Statamic path that isn't config-driven — rebind
        // the singleton so `resources/sites.yaml` lives in the workbench
        // instead of the disposable Testbench skeleton. Safe to rebind here:
        // this provider registers after Statamic's own, and nothing resolves
        // the singleton during registration.
        $this->app->singleton(StatamicSites::class, fn (): Sites => new Sites);

        $this->registerAddonManifest();
        $this->configureStache();

        // Statamic\Version reads the installed version from composer.lock at
        // base_path(), which is the disposable Testbench skeleton — it has
        // none. The CP needs a version to boot, so fake it (same fix
        // AddonTestCase applies via a Version mock in the test suite).
        $this->app->singleton(Version::class, fn (): Version => new class extends Version
        {
            public function get(): string
            {
                return 'dev-main';
            }
        });

        // Statamic's Outpost pings a licensing endpoint through this cache
        // store; the Testbench skeleton's config/cache.php doesn't define
        // it, which only bites when visiting the CP.
        config([
            'cache.stores.outpost' => [
                'driver' => 'file',
                'path' => storage_path('framework/cache/outpost-data'),
            ],
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Statamic pushes this middleware directly onto the resolved Kernel
        // instance (AppServiceProvider::boot(), not the fluent middleware
        // builder in bootstrap/app.php, so it can't be removed there). It
        // throws unless the app's composer.json declares Statamic's
        // pre-update-cmd script, which an addon's composer.json never does.
        $this->app->booted(function (): void {
            $kernel = $this->app->make(Kernel::class);

            $kernel->setGlobalMiddleware(array_values(array_diff(
                $kernel->getGlobalMiddleware(),
                [CheckComposerJsonScripts::class],
            )));
        });
    }

    /**
     * Statamic\Providers\AddonServiceProvider::boot() runs its whole pipeline
     * (tags, config, views, blueprints, middleware, ...) inside a
     * `getAddon()` guard, which resolves the addon from Statamic's package
     * manifest — built only from packages actually listed as dependencies
     * in vendor/composer/installed.json. The addon under development is the
     * workbench's own root package, never a dependency, so it's invisible
     * there and the guard silently no-ops everything.
     *
     * `Statamic\Testing\AddonTestCase` works around exactly this by hand
     * writing the manifest entry in its `getEnvironmentSetUp()` — mirror it
     * here so `composer serve` gets the same tags/config/views the test
     * suite does.
     */
    private function registerAddonManifest(): void
    {
        $composer = json_decode(file_get_contents(package_path('composer.json')), true);

        $namespace = 'MarcoRieser\\Livewire';

        $this->app->make(Manifest::class)->manifest = [
            $composer['name'] => [
                'id' => $composer['name'],
                'slug' => $composer['extra']['statamic']['slug'] ?? null,
                'version' => 'dev-main',
                'namespace' => $namespace,
                'autoload' => $composer['autoload']['psr-4'][$namespace.'\\'],
                'provider' => AddonServiceProvider::class,
            ],
        ];
    }

    /**
     * Point every Stache store at content living in the workbench instead of
     * the disposable Testbench skeleton, so it survives `composer clear`.
     */
    private function configureStache(): void
    {
        $content = workbench_path('content');

        config([
            'statamic.stache.stores.taxonomies.directory' => "{$content}/taxonomies",
            'statamic.stache.stores.terms.directory' => "{$content}/taxonomies",
            'statamic.stache.stores.collections.directory' => "{$content}/collections",
            'statamic.stache.stores.entries.directory' => "{$content}/collections",
            'statamic.stache.stores.navigation.directory' => "{$content}/navigation",
            'statamic.stache.stores.collection-trees.directory' => "{$content}/trees/collections",
            'statamic.stache.stores.nav-trees.directory' => "{$content}/trees/navigation",
            'statamic.stache.stores.globals.directory' => "{$content}/globals",
            'statamic.stache.stores.global-variables.directory' => "{$content}/globals",
            'statamic.stache.stores.asset-containers.directory' => "{$content}/assets",
            'statamic.stache.stores.users.directory' => workbench_path('users'),
        ]);
    }
}
