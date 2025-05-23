<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Statamic\Fields\Fieldtype;

use function Livewire\invade;

class FieldtypeSynthesizer extends Synth
{
    public static string $key = 'slw_fieldtype';

    public static function match($target): bool
    {
        return $target instanceof Fieldtype;
    }

    public function dehydrate(Fieldtype $target, $dehydrateChild): array
    {
        $value = invade($target);
        $reflection = $value->reflected;

        $data = collect($reflection->getProperties())
            ->mapWithKeys(function (\ReflectionProperty $property) use ($value, $reflection) {
                if ($property->isStatic()) {
                    return [
                        $property->getName() => $reflection->getStaticPropertyValue($property->getName()),
                    ];
                }

                return [
                    $property->getName() => $value->{$property->getName()},
                ];
            })
            ->all();

        foreach ($data as $key => $child) {
            $data[$key] = $dehydrateChild($key, $child);
        }

        return [
            $data,
            ['class' => get_class($target)],
        ];
    }

    public function hydrate($value, $meta, $hydrateChild): Fieldtype
    {
        foreach ($value as $key => $child) {
            $value[$key] = $hydrateChild($key, $child);
        }

        $value = collect($value);

        $fieldtype = app($meta['class'])->setField($value->get('field'));

        $fieldtype = invade($fieldtype);

        $value
            ->except('field')
            ->each(fn ($value, $key) => $fieldtype->{$key} = $value);

        return $fieldtype->obj;
    }
}
