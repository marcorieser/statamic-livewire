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

        if (! $cascade = static::cascadeVariables($component)) {
            return;
        }

        $view->with(array_merge($cascade, $data));
    }

    /**
     * @return array<string, mixed>
     */
    public static function cascadeVariables(Component $component): array
    {
        /** @var ?CascadeAttribute $attribute */
        $attribute = $component
            ->getAttributes()
            ->whereInstanceOf(CascadeAttribute::class)
            ->first();

        return $attribute?->getCascadeData() ?? [];
    }

    protected function isUsingAntlers(View $view): bool
    {
        return $view->getEngine() instanceof AntlersEngine;
    }
}
