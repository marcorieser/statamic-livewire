<?php

namespace MarcoRieser\Livewire\Hooks;

use Livewire\ComponentHook;
use MarcoRieser\Livewire\Synthesizers\TransformableSynthesizer;

class TransformSynthesizers extends ComponentHook
{
    public function render($view, $data): void
    {
        if (! config('statamic-livewire.synthesizers.enabled', false) || ! config('statamic-livewire.synthesizers.transform', true)) {
            return;
        }

        collect($data)
            ->map(function ($value) {
                $synthesizer = $this->getMatchingSynthesizer($value);

                return $synthesizer ? call_user_func([$synthesizer, 'transform'], $value) : $value;
            })
            ->each(fn ($value, $key) => $view->with($key, $value));
    }

    protected function getMatchingSynthesizer($value): ?string
    {
        return collect(config('statamic-livewire.synthesizers.classes', []))
            ->filter(fn (string $synthesizer) => is_subclass_of($synthesizer, TransformableSynthesizer::class) && call_user_func([$synthesizer, 'match'], $value))
            ->first();
    }
}
