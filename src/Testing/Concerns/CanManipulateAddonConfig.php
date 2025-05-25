<?php

namespace MarcoRieser\Livewire\Testing\Concerns;

use Illuminate\Support\Arr;

trait CanManipulateAddonConfig
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

    protected function setConfigValue(string $key, $value): void
    {
        $config = config()->array(
            'statamic-livewire',
            require __DIR__.'/../../../config/statamic-livewire.php'
        );

        Arr::set($config, $key, $value);

        config()->set('statamic-livewire', $config);
    }
}
