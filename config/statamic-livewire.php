<?php

return [

    /*
    |--------------------------------------------------------------------------
    | EXPERIMENTAL: Localization
    |--------------------------------------------------------------------------
    |
    | When enabled, the Localize Middleware from Statamic gets applied to
    | Livewire requests and the configured locales per site are handled
    | automatically. This makes the `RestoreCurrentSite` obsolete.
    |
    | This features is experimental. It's meant to be tested and to played
    | with. As long as it is experimental, it can be changed and removed
    | at any point without a warning.
    |
    */

    'localization' => [

        'enabled' => false,

    ],

    /*
    |--------------------------------------------------------------------------
    | EXPERIMENTAL: Livewire Synthesizers
    |--------------------------------------------------------------------------
    |
    | So called synthesizers allow to add custom types to Livewire, which can
    | can be used to make Livewire aware of Statamic classes that you want
    | to work with, as it might make things easier.
    |
    | It's recommended to remove or uncomment those synthesizers that are
    | not used in your application, to avoid overhead by loading those.
    |
    | This features is experimental. It's meant to be tested and to played
    | with. As long as it is experimental, it can be changed and removed
    | at any point without a warning.
    |
    */

    'synthesizers' => [

        'enabled' => false,

        'classes' => [
            \MarcoRieser\Livewire\Synthesizers\EntryCollectionSynthesizer::class,
            \MarcoRieser\Livewire\Synthesizers\EntrySynthesizer::class,
            \MarcoRieser\Livewire\Synthesizers\FieldSynthesizer::class,
            \MarcoRieser\Livewire\Synthesizers\FieldtypeSynthesizer::class,
            \MarcoRieser\Livewire\Synthesizers\ValueSynthesizer::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Replacers
    |--------------------------------------------------------------------------
    |
    | Define the replacers that will be used when static caching is enabled
    | to dynamically replace content within the response.
    |
    */

    'replacers' => [
        \MarcoRieser\Livewire\Replacers\AssetsReplacer::class,
    ],

    'routes' => [
        'update' => 'livewire/update',
    ],
];
