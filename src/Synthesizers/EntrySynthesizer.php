<?php

namespace MarcoRieser\Livewire\Synthesizers;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;
use Statamic\Contracts\Entries\Entry;
use Statamic\Facades\Entry as EntryFacade;

class EntrySynthesizer extends Synth
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
                'collection' => $entry->collection()->handle() ?? null,
                'data' => $entry->data()->all(),
                'date' => $entry->collection()->dated() ? $entry->date() : null,
                'id' => $entry->id(),
                'slug' => $entry->slug() ?? null,
            ], []];
    }

    public function hydrate($value): Entry
    {
        $entry = EntryFacade::make()
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

        return $entry;
    }
}
