<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Carbon\CarbonInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use MarcoRieser\Livewire\Contracts\Synthesizers\AugmentableSynthesizer;
use Statamic\Contracts\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;

class EntrySynthesizer extends Synth implements AugmentableSynthesizer
{
    public static string $key = 'slw_entry';

    public static function match($target): bool
    {
        return $target instanceof EntryContract;
    }

    public static function augment($target): array
    {
        return $target->toAugmentedArray();
    }

    public function dehydrate(EntryContract $entry): array
    {
        return [
            [
                'id' => $entry->id(),
                'collection' => $entry->collection()?->handle(),
                'origin' => $entry->origin()?->id(),
                'blueprint' => $entry->blueprint()?->handle(),
                'locale' => $entry->locale(),
                'initial_path' => $entry->initialPath(),
                'published' => $entry->published(),
                'data' => $entry->data()->all(),
                'slug' => $entry->slug(),
                'date' => $entry->collection()->dated() ? $entry->date() : null,
            ], [],
        ];
    }

    public function hydrate($value): EntryContract
    {
        $entry = Entry::make()
            ->id(Arr::get($value, 'id'))
            ->collection(Arr::get($value, 'collection'))
            ->origin(Arr::get($value, 'origin'))
            ->blueprint(Arr::get($value, 'blueprint'))
            ->locale(Arr::get($value, 'locale'))
            ->initialPath(Arr::get($value, 'initial_path'))
            ->published(Arr::get($value, 'published'))
            ->data(Arr::get($value, 'data'))
            ->slug(Arr::get($value, 'slug'));

        if ($date = Arr::get($value, 'date')) {
            if (! $date instanceof CarbonInterface) {
                $date = Carbon::parse($date);
            }

            $entry->date($date);
        }

        return $entry;
    }
}
