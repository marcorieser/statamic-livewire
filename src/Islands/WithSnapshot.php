<?php

namespace MarcoRieser\Livewire\Islands;

use Exception;
use Livewire\Component;
use Livewire\Mechanisms\HandleComponents\ComponentContext;
use MarcoRieser\Livewire\Exceptions\IslandException;

use function Livewire\trigger;

/**
 * Round-trips island "with" data through Livewire's snapshot pipeline
 * (mirroring how lazy loading persists its mount parameters), so
 * synthesizers dehydrate and rehydrate rich values like dates,
 * collections and Statamic entries.
 */
class WithSnapshot
{
    protected const CONTAINER_COMPONENT = '__antlersIslandWithContainer';

    /**
     * @param  array<string, mixed>  $with
     * @return array<string, mixed>
     */
    public function snapshot(array $with): array
    {
        $this->registerContainerComponent();

        $container = app('livewire')->new(static::CONTAINER_COMPONENT);

        $container->forIsland = $with;

        $context = new ComponentContext($container, mounting: true);

        trigger('dehydrate', $container, $context);

        try {
            return app('livewire')->snapshot($container, $context);
        } catch (Exception $exception) {
            throw new IslandException('The with data of the {{ livewire:island }} tag could not be dehydrated: '.$exception->getMessage(), previous: $exception);
        }
    }

    /**
     * @param  array<string, mixed>  $snapshot
     * @return array<string, mixed>
     */
    public function resurrect(array $snapshot): array
    {
        $this->registerContainerComponent();

        [$container] = app('livewire')->fromSnapshot($snapshot);

        return $container->forIsland;
    }

    protected function registerContainerComponent(): void
    {
        app('livewire')->component(static::CONTAINER_COMPONENT, new class extends Component
        {
            public array $forIsland = [];
        });
    }
}
