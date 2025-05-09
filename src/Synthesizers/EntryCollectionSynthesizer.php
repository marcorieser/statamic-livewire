<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Statamic\Entries\EntryCollection as StatamicEntryCollection;
use Statamic\Facades\Entry;

class EntryCollectionSynthesizer extends TransformableSynthesizer
{
    public static string $key = 'statamic-entry-collection';

    public static function match($target): bool
    {
        return $target instanceof StatamicEntryCollection;
    }

    public function dehydrate($target): array
    {
        $data = [];

        foreach ($target->all() as $entry) {
            $data[] = [
                'collection' => $entry->collection()->handle() ?? null,
                'data' => $entry->data()->all(),
                'date' => $entry->collection()->dated() ? $entry->date() : null,
                'id' => $entry->id(),
                'slug' => $entry->slug() ?? null,
            ];
        }

        return [$data, []];
    }

    public function hydrate($values): StatamicEntryCollection
    {
        $items = [];

        foreach ($values as $value) {
            $entry = Entry::make()
                ->id($value['id'])
                ->slug($value['slug'] ?? null)
                ->collection($value['collection'] ?? null)
                ->data($value['data']);

            if ($value['date']) {
                $date = $value['date'];

                if (! $date instanceof CarbonInterface) {
                    $date = Carbon::parse($date);
                }

                $entry->date($date);
            }

            $items[] = $entry;
        }

        return new StatamicEntryCollection($items);
    }

    public static function transform($target): mixed
    {
        return $target->toAugmentedArray();
    }
}
