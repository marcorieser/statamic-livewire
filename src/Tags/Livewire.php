<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tags;

use InvalidArgumentException;
use Livewire\Component;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use MarcoRieser\Livewire\Islands\IslandManager;
use RuntimeException;
use Statamic\Tags\Tags;

use function Livewire\store;

class Livewire extends Tags
{
    /** @var list<string> */
    protected static $aliases = ['lw', 'wire'];

    /**
     * Named slots collected per component tag pair currently being parsed.
     *
     * @var list<array<string, string>>
     */
    protected static array $slotStack = [];

    /**
     * Mount a Livewire component.
     *
     * {{ livewire:your-component-name }}
     */
    public function wildcard(string $expression): string
    {
        $this->params->put('component', $expression);

        return $this->index();
    }

    /**
     * Mount a Livewire component by parameter.
     *
     * {{ livewire component="your-component-name" }}
     */
    public function index(): string
    {
        $component = $this->params->get('component');

        if (! is_string($component) || $component === '') {
            throw new InvalidArgumentException('The {{ livewire }} tag requires a component name.');
        }

        // Parameters are deep-converted through toArray() so augmentable Statamic
        // objects reach the component as plain values.
        $params = $this->params->except(['key', 'component'])->toArray();

        return \Livewire\Livewire::mount($component, $params, $this->params->get('key'), $this->slots());
    }

    /**
     * Define a named slot inside a component tag pair.
     *
     * {{ livewire:slot name="header" }} ... {{ /livewire:slot }}
     */
    public function slot(): void
    {
        if (self::$slotStack === []) {
            throw new RuntimeException('The {{ livewire:slot }} tag must be used inside a Livewire component tag pair.');
        }

        $name = $this->params->get('name');

        if (! is_string($name) || $name === '') {
            throw new InvalidArgumentException('The {{ livewire:slot }} tag requires a name.');
        }

        self::$slotStack[array_key_last(self::$slotStack)][$name] = trim((string) $this->parse());
    }

    /**
     * Antlers implementation of @island.
     *
     * {{ livewire:island name="stats" }} ... {{ /livewire:island }}
     */
    public function island(): string
    {
        $component = $this->context->value('__livewire');

        if (! $component instanceof Component) {
            throw new RuntimeException('The {{ livewire:island }} tag must be used inside a Livewire component view.');
        }

        $name = $this->params->get('name');

        if (! is_string($name) || $name === '') {
            throw new InvalidArgumentException('The {{ livewire:island }} tag requires a name.');
        }

        $manager = resolve(IslandManager::class);

        $token = $manager->token($component->getName(), $name);

        $manager->store($token, (string) $this->content);

        // Any other parameters become the island's scope. Unlike Blade's
        // with:, the values are captured here — with the surrounding template
        // context available — and persisted through the component memo, so
        // island updates can re-render with them.
        $with = $this->params->except(['name', 'lazy', 'defer', 'always', 'skip'])->toArray();

        $this->registerIsland($component, $name, $token, $with);

        $html = $component->renderIslandDirective(
            name: $name,
            token: $token,
            lazy: $this->boolParam('lazy'),
            defer: $this->boolParam('defer'),
            always: $this->boolParam('always'),
            skip: $this->boolParam('skip'),
        );

        // renderIslandDirective() registers the island again while mounting —
        // drop that duplicate in favor of the entry carrying the scope.
        $this->registerIsland($component, $name, $token, $with);

        return $html;
    }

    /**
     * @param  array<mixed>  $with
     */
    protected function registerIsland(Component $component, string $name, string $token, array $with): void
    {
        $component->setIslands(collect($component->getIslands())
            ->reject(fn (array $island): bool => ($island['name'] ?? null) === $name)
            ->values()
            ->push(array_filter([
                'name' => $name,
                'token' => $token,
                'with' => $with,
            ], fn (mixed $value): bool => $value !== []))
            ->all());
    }

    /**
     * The loading state of a lazy island. Handled by the IslandManager while
     * rendering island sources — reaching this method means the pair was
     * used outside of an island.
     *
     * {{ livewire:placeholder }} ... {{ /livewire:placeholder }}
     */
    public function placeholder(): never
    {
        throw new RuntimeException('The {{ livewire:placeholder }} tag must be used inside a {{ livewire:island }} tag pair.');
    }

    protected function boolParam(string $key): bool
    {
        return filter_var($this->params->get($key, false), FILTER_VALIDATE_BOOL);
    }

    /**
     * Mount a dynamically resolved Livewire component.
     *
     * {{ livewire:component :name="component_name" }}
     */
    public function component(): string
    {
        $this->params->put('component', $this->params->pull('name'));

        return $this->index();
    }

    /**
     * Antlers implementation of @livewireStyles.
     *
     * {{ livewire:styles }}
     */
    public function styles(): string
    {
        return FrontendAssets::styles();
    }

    /**
     * Antlers implementation of @livewireScripts.
     *
     * {{ livewire:scripts }}
     */
    public function scripts(): string
    {
        return FrontendAssets::scripts();
    }

    /**
     * Antlers implementation of @livewireScriptConfig.
     *
     * {{ livewire:scriptConfig }}
     */
    public function scriptConfig(): string
    {
        return FrontendAssets::scriptConfig();
    }

    /**
     * Antlers implementation of @assets.
     *
     * {{ livewire:assets }} ... {{ /livewire:assets }}
     */
    public function assets(): void
    {
        $html = (string) $this->parse();

        // Not a security context: the hash only serves as a short,
        // content-derived deduplication key inside the Livewire payload.
        $key = hash('xxh3', $html);

        // Only load an asset once per request, no matter how often it is used.
        if (in_array($key, SupportScriptsAndAssets::$alreadyRunAssetKeys, true)) {
            return;
        }

        SupportScriptsAndAssets::$alreadyRunAssetKeys[] = $key;

        $component = $this->context->value('__livewire');

        if ($component instanceof Component) {
            store($component)->push('assets', $html, $key);

            return;
        }

        SupportScriptsAndAssets::$nonLivewireAssets[$key] = $html;
    }

    /**
     * Antlers implementation of @script.
     *
     * {{ livewire:script }} ... {{ /livewire:script }}
     */
    public function script(): void
    {
        $component = $this->context->value('__livewire');

        if (! $component instanceof Component) {
            throw new RuntimeException('The {{ livewire:script }} tag must be used inside a Livewire component view.');
        }

        $html = trim((string) $this->parse());

        store($component)->push('scripts', $html, hash('xxh3', $html));
    }

    /**
     * Parse the tag pair content into the component's slots: nested
     * {{ livewire:slot }} pairs become named slots, the remaining
     * content becomes the default slot.
     *
     * @return array<string, string>
     */
    protected function slots(): array
    {
        if (! $this->isPair) {
            return [];
        }

        self::$slotStack[] = [];

        try {
            $default = trim((string) $this->parse());
        } finally {
            $slots = array_pop(self::$slotStack);
        }

        if ($default !== '') {
            $slots['default'] = $default;
        }

        return $slots;
    }
}
