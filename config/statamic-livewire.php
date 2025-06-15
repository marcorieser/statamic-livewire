<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Localization
    |--------------------------------------------------------------------------
    |
    | When enabled, the Localize Middleware from Statamic gets applied to
    | Livewire requests, and the configured locales per site are handled
    | automatically.
    |
    */

    'localization' => true,

    /*
    |--------------------------------------------------------------------------
    | Synthesizers
    |--------------------------------------------------------------------------
    |
    | Synthesizers allow adding custom types to Livewire, which can
    | can be used to make Livewire aware of Statamic classes that you want
    | to work with, as it might make things easier.
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

        'augmentation' => true,
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
];
