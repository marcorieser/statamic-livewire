<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use MarcoRieser\Livewire\Contracts\Synthesizers\AugmentableSynthesizer;
use Statamic\Fields\Field;

use function Livewire\invade;

class FieldSynthesizer extends Synth implements AugmentableSynthesizer
{
    public static string $key = 'slw_field';

    public static function match($target): bool
    {
        return $target instanceof Field;
    }

    public static function augment($target): mixed
    {
        return $target->augment();
    }

    public function dehydrate(Field $target, $dehydrateChild): array
    {
        $value = invade($target);

        $data = collect($value->reflected->getProperties())
            ->mapWithKeys(fn (\ReflectionProperty $property) => [
                $property->getName() => $value->{$property->getName()},
            ])
            ->all();

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

        $value = collect($value);

        $field = new Field(...$value->only('handle', 'config')->all());

        $field = invade($field);

        $value
            ->except('handle', 'config')
            ->each(fn ($value, $key) => $field->{$key} = $value);

        return $field->obj;
    }
}
