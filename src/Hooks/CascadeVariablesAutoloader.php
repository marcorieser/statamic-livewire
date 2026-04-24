<?php

namespace MarcoRieser\Livewire\Hooks;

use Livewire\Component;
use Livewire\ComponentHook;
use Livewire\Livewire;
use MarcoRieser\Livewire\Attributes\Cascade as CascadeAttribute;

class CascadeVariablesAutoloader extends ComponentHook
{
    public function render($view, $data): void
    {
        /** @var Component $component */
        if (! ($component = Livewire::current())) {
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

        $view->with(array_merge($attribute->data(), $data));
    }
}
