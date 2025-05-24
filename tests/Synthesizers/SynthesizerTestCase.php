<?php

namespace MarcoRieser\Livewire\Tests\Synthesizers;

use MarcoRieser\Livewire\Tests\TestCase;

abstract class SynthesizerTestCase extends TestCase
{
    protected function enableSynthesizers(): void
    {
        $this->setConfigValue('synthesizers.enabled', true);
    }

    protected function disableSynthesizers(): void
    {
        $this->setConfigValue('synthesizers.enabled', false);
    }

    protected function disableSynthesizerTransform(): void
    {
        $this->setConfigValue('synthesizers.transform', false);
    }

    protected function enableSynthesizerTransform(): void
    {
        $this->setConfigValue('synthesizers.transform', true);
    }
}
