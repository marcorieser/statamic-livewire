<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Hooks;

use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\ComponentHook;
use Livewire\Features\SupportAttributes\Attribute;
use Statamic\Fields\Value;
use Statamic\View\Antlers\Engine as AntlersEngine;

class ComputedPropertiesAutoloader extends ComponentHook
{
    /**
     * Make #[Computed] properties available as variables in Antlers component
     * views. Each property is wrapped in a lazy Value so it only executes
     * when the view actually uses it.
     *
     * @param  array<string, mixed>  $data
     */
    public function render(View $view, array $data): void
    {
        if (! $view->getEngine() instanceof AntlersEngine) {
            return;
        }

        $component = $this->component;

        $computed = collect($component->getAttributes())
            ->filter(fn (Attribute $attribute): bool => $attribute instanceof Computed)
            ->mapWithKeys(function (Computed $attribute) use ($component): array {
                $name = $attribute->getName();

                return [$name => new Value(fn () => $component->{$name})];
            })
            ->all();

        $view->with([...$data, ...$computed]);
    }
}
