<?php

namespace MarcoRieser\Livewire\Hooks;

use Illuminate\Contracts\View\View;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;

/**
 * Re-renders the component's Antlers view when island cache files have been
 * cleared, so the {{ livewire:island }} tags rewrite them before Livewire
 * looks for them.
 */
class AntlersIslandsRegenerator extends ComponentHook
{
    public function hydrate($memo): void
    {
        $missing = collect($memo['islands'] ?? [])
            ->filter(fn (array $island) => str_starts_with($island['token'] ?? '', 'antlers-'))
            ->contains(fn (array $island) => ! file_exists(IslandCompiler::getCachedPathFromToken($island['token'])));

        if ($missing) {
            $this->regenerateAntlersIslandCacheFiles();
        }
    }

    protected function regenerateAntlersIslandCacheFiles(): void
    {
        $view = $this->component->render();

        if (! $view instanceof View) {
            return;
        }

        $view->with(array_merge(
            Utils::getPublicPropertiesDefinedOnSubclass($this->component),
            ['__livewire' => $this->component],
        ))->render();
    }
}
