<?php

namespace MarcoRieser\Livewire\Contracts\Synthesizers;

interface AugmentableSynthesizer
{
    public static function augment($target): mixed;
}
