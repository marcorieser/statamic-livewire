<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Statamic\Entries\EntryCollection as StatamicEntryCollection;

class EntryCollectionSynthesizer extends TransformableSynthesizer
{
    public static string $key = 'statamic-entry-collection';

    public static function match($target): bool
    {
        return $target instanceof StatamicEntryCollection;
    }

    public function dehydrate($target, $dehydrateChild): array
    {
        $data = $target->all();

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [$data, []];
    }

    public function hydrate($value, $meta, $hydrateChild): StatamicEntryCollection
    {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return new StatamicEntryCollection($value);
    }

    public static function transform($target): mixed
    {
        return $target->toAugmentedArray();
    }

    public function &get(&$target, $key)
    {
        // We need this "$reader" callback to get a reference to
        // the items property inside collections. Otherwise,
        // we'd receive a copy instead of the reference.
        $reader = function &($object, $property) {
            $value = &\Closure::bind(function &() use ($property) {
                return $this->$property;
            }, $object, $object)->__invoke();

            return $value;
        };

        $items = &$reader($target, 'items');

        return $items[$key];
    }
}
