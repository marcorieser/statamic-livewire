<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tags;

use InvalidArgumentException;
use Statamic\Tags\Tags;

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
        // objects reach the component as plain values. The opt-in synthesizer
        // feature will switch this to all() to hand over the raw objects.
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
}
