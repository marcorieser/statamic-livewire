<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MarcoRieser\Livewire\Livewire
 */
class Livewire extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \MarcoRieser\Livewire\Livewire::class;
    }
}
