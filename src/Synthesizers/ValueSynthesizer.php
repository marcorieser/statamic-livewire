<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Statamic\Fields\Value;

use function Livewire\invade;

class ValueSynthesizer extends AbstractSynthesizer
{
    public static string $key = 'statamic-value';

    public static function match($target): bool
    {
        return $target instanceof Value;
    }

    public function dehydrate($target, $dehydrateChild): array
    {
        $value = invade($target);

        $data = [
            'value' => $value->raw,
            'handle' => $value->handle,
            'fieldtype' => $value->fieldtype,
            'augmentable' => $value->augmentable,
            'shallow' => $value->shallow,
        ];

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [$data, []];
    }

    public function hydrate($value, $meta, $hydrateChild): Value
    {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return new Value(...$value);
    }

    public static function transform($target): mixed
    {
        return $target;
    }
}
