<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use MarcoRieser\Livewire\Contracts\Synthesizers\AugmentableSynthesizer;
use Statamic\Entries\EntryCollection;

class EntryCollectionSynthesizer extends Synth implements AugmentableSynthesizer
{
    public static string $key = 'slw_entryco';

    public static function match($target): bool
    {
        return $target instanceof EntryCollection;
    }

    public static function augment($target): array
    {
        return $target->toAugmentedArray();
    }

    public function dehydrate(EntryCollection $target, $dehydrateChild): array
    {
        $data = $target->all();

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [$data, []];
    }

    public function hydrate($value, $meta, $hydrateChild): EntryCollection
    {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return new EntryCollection($value);
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
