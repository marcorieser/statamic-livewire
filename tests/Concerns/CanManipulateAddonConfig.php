<?php

namespace MarcoRieser\Livewire\Tests\Concerns;

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

    protected function disableSynthesizerAugmentation(): void
    {
        $this->setConfigValue('synthesizers.augmentation', false);
    }

    protected function enableSynthesizerAugmentation(): void
    {
        $this->setConfigValue('synthesizers.augmentation', true);
    }

    protected function setConfigValue(string $key, $value): void
    {
        $config = config()->array(
            'statamic-livewire',
            require __DIR__.'/../../config/statamic-livewire.php'
        );

        Arr::set($config, $key, $value);

        config()->set('statamic-livewire', $config);
    }
}
