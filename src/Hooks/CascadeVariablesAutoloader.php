<?php

namespace MarcoRieser\Livewire\Hooks;

use Illuminate\View\View;
use Livewire\Component;
use Livewire\ComponentHook;
use Livewire\Livewire;
use MarcoRieser\Livewire\Attributes\Cascade as CascadeAttribute;
use Statamic\View\Antlers\Engine as AntlersEngine;

class CascadeVariablesAutoloader extends ComponentHook
{
    public function render($view, $data): void
    {
        /** @var Component $component */
        if (! ($component = Livewire::current())) {
            return;
        }

        if (! $this->isUsingAntlers($view)) {
            return;
        }

        /** @var ?CascadeAttribute $attribute */
        $attribute = $component
            ->getAttributes()
            ->whereInstanceOf(CascadeAttribute::class)
            ->first();

        if (! $attribute) {
            return;
        }

        $cascade = $attribute->getCascadeData();

        $view->with(array_merge($cascade, $data));
    }

    protected function isUsingAntlers(View $view): bool
    {
        return $view->getEngine() instanceof AntlersEngine;
    }
}
