<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

abstract class AbstractSynthesizer extends Synth
{
    abstract public static function transform($target): mixed;
}
