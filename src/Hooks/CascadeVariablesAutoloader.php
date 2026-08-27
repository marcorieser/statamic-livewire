<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Hooks;

use Illuminate\View\View;
use Livewire\ComponentHook;
use Livewire\Features\SupportAttributes\Attribute;
use MarcoRieser\Livewire\Attributes\Cascade;
use Statamic\View\Antlers\Engine as AntlersEngine;

class CascadeVariablesAutoloader extends ComponentHook
{
    /**
     * Make the Statamic cascade available as variables in Antlers component
     * views of components carrying the #[Cascade] attribute.
     *
     * @param  array<string, mixed>  $data
     */
    public function render(View $view, array $data): void
    {
        if (! $view->getEngine() instanceof AntlersEngine) {
            return;
        }

        $attribute = collect($this->component->getAttributes())
            ->first(fn (Attribute $attribute): bool => $attribute instanceof Cascade);

        if (! $attribute instanceof Cascade) {
            return;
        }

        $view->with([...$attribute->getCascadeData(), ...$data]);
    }
}
