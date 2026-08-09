<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tags;

use InvalidArgumentException;
use Livewire\Component;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use RuntimeException;
use Statamic\Tags\Tags;

use function Livewire\store;

class Livewire extends Tags
{
    /** @var list<string> */
    protected static $aliases = ['lw', 'wire'];

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

        return \Livewire\Livewire::mount($component, $params, $this->params->get('key'));
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
}
