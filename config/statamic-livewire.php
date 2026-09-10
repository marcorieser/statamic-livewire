<?php

use MarcoRieser\Livewire\Replacers\AssetsReplacer;
use MarcoRieser\Livewire\Synthesizers\EntryCollectionSynthesizer;
use MarcoRieser\Livewire\Synthesizers\EntrySynthesizer;
use MarcoRieser\Livewire\Synthesizers\FieldSynthesizer;
use MarcoRieser\Livewire\Synthesizers\FieldtypeSynthesizer;
use MarcoRieser\Livewire\Synthesizers\ValueSynthesizer;

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
            EntryCollectionSynthesizer::class,
            EntrySynthesizer::class,
            FieldSynthesizer::class,
            FieldtypeSynthesizer::class,
            ValueSynthesizer::class,
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
        AssetsReplacer::class,
    ],
];
