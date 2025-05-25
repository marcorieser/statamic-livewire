<?php

namespace MarcoRieser\Livewire\Hooks;

use Livewire\ComponentHook;
use MarcoRieser\Livewire\Contracts\Synthesizers\AugmentableSynthesizer;

class SynthesizerAugmentor extends ComponentHook
{
    public function render($view, $data): void
    {
        if (! config()->boolean('statamic-livewire.synthesizers.enabled', false)) {
            return;
        }

        if (! config()->boolean('statamic-livewire.synthesizers.augmentation', true)) {
            return;
        }

        $view->with($this->augment($data));
    }

    protected function getMatchingSynthesizer($value): ?string
    {
        return collect(config()->array('statamic-livewire.synthesizers.classes', []))
            ->filter(fn (string $synthesizer) => is_subclass_of($synthesizer, AugmentableSynthesizer::class) && call_user_func([$synthesizer, 'match'], $value))
            ->first();
    }

    protected function augment(array $data): array
    {
        return collect($data)
            ->map(function ($value) {
                $synthesizer = $this->getMatchingSynthesizer($value);

                return $synthesizer ? call_user_func([$synthesizer, 'augment'], $value) : $value;
            })
            ->all();
    }
}
