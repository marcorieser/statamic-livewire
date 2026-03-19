<?php

namespace MarcoRieser\Livewire\Tags;

use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
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
