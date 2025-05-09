<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Statamic\Fields\Field;

class FieldSynthesizer extends TransformableSynthesizer
{
    public static string $key = 'statamic-field';

    public static function match($target): bool
    {
        return $target instanceof Field;
    }

    public function dehydrate($target, $dehydrateChild): array
    {
        $data = [
            'handle' => $target->handle(),
            'config' => $target->config(),
        ];

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [$data, []];
    }

    public function hydrate($value, $meta, $hydrateChild): Field
    {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return new Field(...$value);
    }

    public static function transform($target): mixed
    {
        return $target->augment();
    }
}
