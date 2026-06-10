<?php

namespace MarcoRieser\Livewire\Tags;

use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use MarcoRieser\Livewire\Exceptions\IslandException;
use MarcoRieser\Livewire\Islands\IslandManager;
use Statamic\Support\Str;
use Statamic\Tags\Tags;

use function Livewire\store;

class Livewire extends Tags
{
    protected static $aliases = ['lw', 'wire'];

    /**
     * This will load your Livewire component in the Antlers view
     *
     * {{ livewire:your-component-name }}
     */
    public function wildcard($expression)
    {
        if (Str::startsWith($expression, 'computed:')) {
            $this->params->put('property', Str::after($expression, 'computed:'));

            return $this->computed();
        }

        $this->params->put('component', $expression);

        return $this->index();
    }

    /**
     * This will load your Livewire component in the Antlers view
     *
     * {{ livewire component="your-component-name" }}
     */
    public function index()
    {
        if (! ($component = $this->params->get('component'))) {
            return null;
        }

        $params = $this->params->except(['key', 'component']);
        $params = config()->boolean('statamic-livewire.synthesizers.enabled', false) ? $params->all() : $params->toArray();

        return \Livewire\Livewire::mount($component, $params, $this->params->only('key')->first());
    }

    /**
     * This will load your Livewire component in the Antlers view
     *
     * {{ livewire:component name="my-component" }}
     */
    public function component(): string
    {
        $this->params->put('component', $this->params->pull('name'));

        return $this->index();
    }

    /**
     * Antlers implementation of @livewireStyles
     *
     * {{ livewire:styles }}
     */
    public function styles(): string
    {
        return FrontendAssets::styles();
    }

    /**
     * Antlers implementation of @livewireScripts
     *
     * {{ livewire:scripts }}
     */
    public function scripts(): string
    {
        return FrontendAssets::scripts();
    }

    /**
     * Antlers implementation of @livewireScriptConfig
     *
     * {{ livewire:scriptConfig }}
     */
    public function scriptConfig(): string
    {
        return FrontendAssets::scriptConfig();
    }

    /**
     * Antlers implementation of @assets - https://livewire.laravel.com/docs/javascript#loading-assets
     *
     * {{ livewire:assets }}...{{ /livewire:assets }}
     */
    public function assets(): void
    {
        $html = (string) $this->parse();

        $key = md5($html);

        if (in_array($key, SupportScriptsAndAssets::$alreadyRunAssetKeys)) {
            // Skip it...
        } else {
            SupportScriptsAndAssets::$alreadyRunAssetKeys[] = $key;
            store($this->context['__livewire'])->push('assets', $html, $key);
        }
    }

    /**
     * Antlers implementation of @script - https://livewire.laravel.com/docs/javascript#executing-scripts
     *
     * {{ livewire:script }}...{{ /livewire:script }}
     */
    public function script(): void
    {
        $html = trim((string) $this->parse());

        $key = md5($html);

        store($this->context['__livewire'])->push('scripts', $html, $key);
    }

    /**
     * Antlers implementation of @island - https://livewire.laravel.com/docs/4.x/islands
     *
     * {{ livewire:island name="stats" }}...{{ /livewire:island }}
     * {{ livewire:island name="stats" defer="true" }}...{{ /livewire:island }}
     */
    public function island(): string
    {
        throw_unless($this->isPair, new IslandException('The {{ livewire:island }} tag has to be used as a tag pair.'));

        throw_unless($name = $this->params->get('name'), new IslandException('The {{ livewire:island }} tag requires a name parameter.'));

        throw_unless(preg_match('/^[\w.\-]+$/', $name), new IslandException('The {{ livewire:island }} name may only contain letters, numbers, underscores, dashes and dots.'));

        throw_unless($component = $this->context->value('__livewire'), new IslandException('The {{ livewire:island }} tag can only be used inside the Antlers view of a Livewire component.'));

        throw_unless(method_exists($component, 'renderIslandDirective'), new IslandException('Islands require Livewire v4.'));

        $with = $this->params->get('with') ?? [];

        throw_unless(is_array($with), new IslandException('The with parameter of the {{ livewire:island }} tag has to be an array.'));

        $token = app(IslandManager::class)->ensureIslandCacheFile($component, $name, $this->content, $with);

        $mounting = $component->islandIsMounting();

        $html = $component->renderIslandDirective(
            name: $name,
            token: $token,
            lazy: $this->params->bool('lazy'),
            defer: $this->params->bool('defer'),
            always: $this->params->bool('always'),
            skip: $this->params->bool('skip'),
        );

        if ($mounting) {
            app(IslandManager::class)->persistWithSnapshots($component);
        }

        return $html;
    }

    /**
     * Antlers implementation of @this
     *
     * {{ livewire:this }}
     * {{ livewire:this set="('name', 'Jack')" }}
     *
     * @deprecated
     */
    public function this(): string
    {
        $instanceId = $this->context['__livewire']->getId();

        if (! count($this->params)) {
            return "window.Livewire.find('$instanceId')";
        }

        $action = $this->params->take(1)->toArray();
        $method = key($action);
        $parameters = reset($action);

        return "window.Livewire.find('$instanceId').$method$parameters";
    }

    /**
     * Antlers implementation of @entangle
     *
     * {{ livewire:entangle property="showDropdown" modifier="live" }}
     *
     * @deprecated
     */
    public function entangle(): string
    {
        $property = $this->params->get('property');
        $modifier = $this->params->get('modifier');
        $instanceId = $this->context['__livewire']->getId();

        $expression = ".entangle('$property')";

        if ($modifier) {
            $expression .= ".$modifier";
        }

        return "window.Livewire.find('$instanceId')$expression";
    }
}
