<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Statamic\Fields\Fieldtype;

class FieldtypeSynthesizer extends TransformableSynthesizer
{
    public static string $key = 'statamic-fieldtype';

    public static function match($target): bool
    {
        return $target instanceof Fieldtype;
    }

    public function dehydrate($target, $dehydrateChild): array
    {
        $data = [
            'field' => $target->field(),
        ];

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [
            $data,
            ['class' => get_class($target)],
        ];
    }

    public function hydrate($value, $meta, $hydrateChild)
    {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        return app($meta['class'])->setField($value['field']);
    }
}
