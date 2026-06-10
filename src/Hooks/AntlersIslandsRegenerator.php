<?php

namespace MarcoRieser\Livewire\Hooks;

use Illuminate\Contracts\View\View;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use MarcoRieser\Livewire\Islands\IslandManager;

use function Livewire\trigger;

/**
 * Re-renders the component's Antlers view when island cache files have been
 * cleared, so the {{ livewire:island }} tags rewrite them before Livewire
 * looks for them.
 */
class AntlersIslandsRegenerator extends ComponentHook
{
    /**
     * Island occurrences are counted per render pass, so each pass (including
     * the regeneration pass below) starts from zero.
     */
    public function render($view, $data): void
    {
        app(IslandManager::class)->resetRootOccurrences($this->component);
    }

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
     * not have run yet) so the re-rendered tags reuse the memoized tokens. The
     * render is driven through Livewire's render trigger so component hooks
     * (like the addon's computed property and Cascade autoloaders) provide the
     * same view data as on a regular render.
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

        $properties = Utils::getPublicPropertiesDefinedOnSubclass($this->component);

        $view->with(array_merge($properties, ['__livewire' => $this->component]));

        $finish = trigger('render', $this->component, $view, $properties);

        $html = $view->render();

        $replaceHtml = function ($newHtml) use (&$html) {
            $html = $newHtml;
        };

        $finish($html, $replaceHtml);
    }
}
