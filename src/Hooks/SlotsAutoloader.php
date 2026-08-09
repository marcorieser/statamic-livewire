<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Hooks;

use Illuminate\View\View;
use Livewire\Component;
use Statamic\View\Antlers\Engine as AntlersEngine;

class SlotsAutoloader
{
    /**
     * Make the component's slots renderable in Antlers views as an array of
     * html keyed by slot name ({{ slots:default }}, {{ slots:header }}, …) —
     * Livewire's own slot proxy only supports Blade. Registered as a plain
     * render listener so it runs after Livewire's slot support.
     *
     * @param  array<string, mixed>  $data
     */
    public function __invoke(Component $component, View $view, array $data): void
    {
        if (! $view->getEngine() instanceof AntlersEngine) {
            return;
        }

        $slots = [];

        foreach ($component->getSlots() as $slot) {
            $slots[$slot->getName()] = $slot->toHtml();
        }

        if ($slots === []) {
            return;
        }

        $view->with(['slots' => $slots]);
    }
}
