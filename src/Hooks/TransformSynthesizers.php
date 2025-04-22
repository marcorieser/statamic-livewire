<?php

namespace MarcoRieser\Livewire\Hooks;

use Livewire\ComponentHook;

class TransformSynthesizers extends ComponentHook
{
    public function render($view, $data): void
    {
        if (! config('statamic-livewire.synthesizers.enabled', false) || ! config('statamic-livewire.synthesizers.transform', true)) {
            return;
        }

        collect($data)
            ->map(function ($value) {
                $synthesizer = collect(config('statamic-livewire.synthesizers.classes', []))
                    ->filter(fn (string $synthesizer) => call_user_func([$synthesizer, 'match'], $value))
                    ->first();

                return $synthesizer ? call_user_func([$synthesizer, 'transform'], $value) : $value;
            })
            ->each(fn ($value, $key) => $view->with($key, $value));
    }
}
