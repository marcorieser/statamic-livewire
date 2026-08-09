<?php

declare(strict_types=1);
use MarcoRieser\Livewire\Replacers\AssetsReplacer;
use MarcoRieser\Livewire\Replacers\DisableBackButtonCacheReplacer;

return [

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | When enabled, the current site is resolved from the original page URL
    | on Livewire update requests and Statamic's Localize middleware is
    | applied, so multi-site locales are handled automatically.
    |
    */

    'localization' => true,

    /*
    |--------------------------------------------------------------------------
    | Replacers
    |--------------------------------------------------------------------------
    |
    | Replacers keep Livewire working on statically cached pages: they bake
    | the assets into cached responses and restore the back/forward-cache
    | protection headers on cached pages containing components.
    |
    */

    'replacers' => [
        AssetsReplacer::class,
        DisableBackButtonCacheReplacer::class,
    ],

];
