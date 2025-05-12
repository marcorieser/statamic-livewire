<?php

namespace MarcoRieser\Livewire\Hooks;

use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\ComponentHook;
use Livewire\Features\SupportAttributes\Attribute;
use Livewire\Livewire;
use Statamic\Fields\Value;
use Statamic\View\Antlers\Engine;

class ComputedPropertiesAutoloader extends ComponentHook
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

        $computed = $component->getAttributes()
            ->filter(fn (Attribute $attribute) => $attribute instanceof Computed)
            ->flatMap(fn (Computed $attribute) => [$attribute->getName() => new Value(fn () => $component->{$attribute->getName()})])
            ->all();

        $view->with(array_merge($data, $computed));
    }

    protected function isUsingAntlers(View $view): bool
    {
        return $view->getEngine() instanceof Engine;
    }
}
