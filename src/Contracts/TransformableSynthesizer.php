<?php

namespace MarcoRieser\Livewire\Contracts;

interface TransformableSynthesizer
{
    public static function transform($target): mixed;
}
