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

        $this->injectComputedProperties($view, $data);
    }

    /**
     * Islands render through Livewire's own renderIsland() call, which fires
     * a separate hook from the component's main render() — without this,
     * computed properties are never available inside island views. Islands
     * always render through a generated Blade shim (never AntlersEngine
     * directly, even for Antlers islands, since IslandManager::render()
     * parses the Antlers source itself), so Antlers islands are identified
     * by their token prefix instead — see IslandManager::token().
     *
     * @param  array<string, mixed>  $data
     */
    public function renderIsland(string $name, View $view, array $data): void
    {
        $isAntlersIsland = collect($this->component->getIslands())->contains(
            fn (array $island): bool => $island['name'] === $name
                && is_string($island['token'] ?? null)
                && str_starts_with($island['token'], 'antlers-'),
        );

        if (! $isAntlersIsland) {
            return;
        }

        $this->injectComputedProperties($view, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function injectComputedProperties(View $view, array $data): void
    {
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
