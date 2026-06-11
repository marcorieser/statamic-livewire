<?php

namespace MarcoRieser\Livewire\Hooks;

use Illuminate\Contracts\View\View;
use Livewire\ComponentHook;
use Livewire\Drawer\Utils;
use Livewire\Features\SupportIslands\Compiler\IslandCompiler;
use MarcoRieser\Livewire\Islands\IslandManager;

use function Livewire\trigger;
use function Livewire\wrap;

/**
 * Re-renders the component's Antlers view when island cache files have been
 * cleared, so the {{ livewire:island }} tags rewrite them before Livewire
 * looks for them.
 */
class AntlersIslandsRegenerator extends ComponentHook
{
    /**
     * Island occurrences are counted per render pass and root tokens are
     * scoped to the rendered view, so each pass (including the regeneration
     * pass below) registers its view and counts from zero.
     */
    public function render($view, $data): void
    {
        app(IslandManager::class)->startRenderPass(
            $this->component,
            $view instanceof View ? (string) $view->name() : '',
        );
    }

    /**
     * Regeneration happens at call time instead of hydrate time: every island
     * render on subsequent requests (action islands, lazy-load mounts and
     * mid-method streamIsland calls) is reached through a method call, and by
     * then the component's own boot()/hydrate() lifecycle hooks have
     * initialized any state the re-render depends on.
     */
    public function call($method, $params, $returnEarly, $metadata): void
    {
        $islands = $this->component->getIslands();

        $missing = collect($islands)
            ->filter(fn (array $island) => str_starts_with($island['token'] ?? '', 'antlers-'))
            ->contains(fn (array $island) => ! file_exists(IslandCompiler::getCachedPathFromToken($island['token'])));

        if ($missing) {
            $this->regenerateAntlersIslandCacheFiles($islands);
        }
    }

    /**
     * The render is driven through Livewire's render trigger so component
     * hooks (like the addon's computed property and Cascade autoloaders)
     * provide the same view data as on a regular render.
     *
     * @param  array<int, array{name: string, token: string}>  $islands
     */
    protected function regenerateAntlersIslandCacheFiles(array $islands): void
    {
        $view = $this->resolveComponentView();

        if ($view instanceof View) {
            $properties = Utils::getPublicPropertiesDefinedOnSubclass($this->component);

            $view->with(array_merge($properties, ['__livewire' => $this->component]));

            $finish = trigger('render', $this->component, $view, $properties);

            $html = $view->render();

            $replaceHtml = function ($newHtml) use (&$html) {
                $html = $newHtml;
            };

            $finish($html, $replaceHtml);
        }

        $this->regenerateNestedIslandCacheFiles($islands);
    }

    /**
     * Components may define render() or provide their view through view(),
     * mirroring the view resolution of Livewire's normal render path.
     */
    protected function resolveComponentView(): ?View
    {
        if (method_exists($this->component, 'render')) {
            $view = wrap($this->component)->render();
        } elseif ($this->component->hasProvidedView()) {
            $view = $this->component->getProvidedView();
        } else {
            $view = null;
        }

        return $view instanceof View ? $view : null;
    }

    /**
     * Nested {{ livewire:island }} tags only execute while their containing
     * island renders, so the islands whose cache files exist are rendered
     * (output discarded) until every cache file is back, one nesting level
     * per round.
     *
     * @param  array<int, array{name: string, token: string}>  $islands
     */
    protected function regenerateNestedIslandCacheFiles(array $islands): void
    {
        $islands = collect($islands)
            ->filter(fn (array $island) => str_starts_with($island['token'] ?? '', 'antlers-'));

        $rendered = [];

        while ($islands->contains(fn (array $island) => ! file_exists(IslandCompiler::getCachedPathFromToken($island['token'])))) {
            $renderable = $islands->filter(fn (array $island) => ! in_array($island['token'], $rendered)
                && file_exists(IslandCompiler::getCachedPathFromToken($island['token'])));

            if ($renderable->isEmpty()) {
                return;
            }

            $renderable->each(function (array $island) use (&$rendered) {
                $rendered[] = $island['token'];

                $this->component->renderIslandView($island['name'], $island['token']);
            });
        }
    }
}
