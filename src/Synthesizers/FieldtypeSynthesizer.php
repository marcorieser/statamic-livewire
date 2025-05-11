<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Statamic\Fields\Fieldtype;

class FieldtypeSynthesizer extends Synth
{
    public static string $key = 'slw_fieldtype';

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
