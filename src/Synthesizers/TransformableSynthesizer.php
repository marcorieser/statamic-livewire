<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

abstract class TransformableSynthesizer extends Synth
{
    public static function transform($target): mixed
    {
        return $target;
    }
}
