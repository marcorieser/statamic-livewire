<?php

namespace MarcoRieser\Livewire\Tags;

use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use MarcoRieser\Livewire\Helpers\DataFetcher;
use Statamic\Support\Str;
use Statamic\Tags\Tags;

use function Livewire\store;

class Livewire extends Tags
{
    protected static $aliases = ['lw'];

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

        return \Livewire\Livewire::mount($component, $this->params->except('key')->toArray(), $this->params->only('key')->first());
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
     * This will return the value of a computed property.
     *
     * {{ livewire:computed:my_computed_property }}
     *
     * @deprecated
     */
    public function computed()
    {
        if (! ($property = $this->params->get(['property', 'prop']))) {
            return null;
        }

        $property = Str::replace([':', '.'], ':', $property);
        $path = collect(explode(':', $property));
        $property = $path->shift();

        $property = collect([$property, Str::camel($property), Str::snake($property)])
            ->filter(fn (string $property) => method_exists(\Livewire\Livewire::current(), $property))
            ->first();

        if (! $property) {
            return null;
        }

        $path->prepend($property);

        return DataFetcher::getValue($path->join(':'), [$property => \Livewire\Livewire::current()->$property]);
    }

    /**
     * Sharing State Between Livewire And Alpine via entangle.
     *
     * {{ livewire:entangle property="showDropdown" modifier="live" }}
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

    /**
     * Accessing the Livewire component.
     *
     * {{ livewire:this }}
     * {{ livewire:this set="('name', 'Jack')" }}
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
     * Loading the livewire styles in antlers style
     *
     * {{ livewire:styles }}
     */
    public function styles(): string
    {
        return FrontendAssets::styles();
    }

    /**
     * Loading the livewire scripts in antlers style
     *
     * {{ livewire:scripts }}
     */
    public function scripts(): string
    {
        return FrontendAssets::scripts();
    }

    /**
     * Prevent livewire from auto-injecting styles and scripts
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
     * {{ livewire:assets }}....{{ /livewire:assets }}
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
}
