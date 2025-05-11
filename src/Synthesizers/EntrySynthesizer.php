<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;

class EntrySynthesizer extends TransformableSynthesizer
{
    public static string $key = 'statamic-entry';

    public static function match($target): bool
    {
        return $target instanceof Entry;
    }

    public function dehydrate(Entry $entry): array
    {
        return [
            [
                'id' => $entry->id(),
                'slug' => $entry->slug() ?? null,
                'collection' => $entry->collection()?->handle() ?? null,
                'blueprint' => $entry->blueprint()?->handle() ?? null,
                'locale' => $entry->locale(),
                'data' => $entry->data()->all(),
                'date' => $entry->collection()->dated() ? $entry->date() : null,
            ], []];
    }

    public function hydrate($value): Entry
    {
        $entry = EntryFacade::make()
            ->id($value['id'] ?? null)
            ->slug($value['slug'] ?? null)
            ->collection($value['collection'] ?? null)
            ->blueprint($value['blueprint'] ?? null)
            ->locale($value['locale'] ?? null)
            ->data($value['data']);

        if ($value['date']) {
            $date = $value['date'];

            if (! $date instanceof CarbonInterface) {
                $date = Carbon::parse($date);
            }

            $entry->date($date);
        }

        return $entry;
    }

    public static function transform($target): mixed
    {
        return $target->toAugmentedArray();
    }
}
