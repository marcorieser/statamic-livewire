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
        $islands = $memo['islands'] ?? [];

        $missing = collect($islands)
            ->filter(fn (array $island) => str_starts_with($island['token'] ?? '', 'antlers-'))
            ->contains(fn (array $island) => ! file_exists(IslandCompiler::getCachedPathFromToken($island['token'])));

        if ($missing) {
            $this->regenerateAntlersIslandCacheFiles($islands);
        }
    }

    /**
     * The island state is restored up front (core's SupportIslands hydrate may
     * not have run yet) so the re-rendered tags reuse the memoized tokens.
     *
     * @param  array<int, array{name: string, token: string}>  $islands
     */
    protected function regenerateAntlersIslandCacheFiles(array $islands): void
    {
        $this->component->markIslandsAsMounted();
        $this->component->setIslands($islands);

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
