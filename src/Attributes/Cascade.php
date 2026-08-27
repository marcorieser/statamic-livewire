<?php

namespace MarcoRieser\Livewire\Attributes;

use Illuminate\Support\Arr;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Statamic\Exceptions\CascadeDataNotFoundException;
use Statamic\Facades\Cascade as CascadeFacade;

#[\Attribute]
class Cascade extends LivewireAttribute
{
    public function __construct(public array $keys = []) {}

    public function getCascadeData(): array
    {
        $data = CascadeFacade::toArray();

        // 'current_url' only set by hydration, unlike 'views', which Antlers
        // writes as a side effect of rendering any view.
        if (! array_key_exists('current_url', $data)) {
            $data = CascadeFacade::hydrate()->toArray();
        }

        if (! $this->keys) {
            return $data;
        }

        return collect($this->keys)
            ->mapWithKeys(function ($default, $key) use ($data) {
                if (is_numeric($key)) {
                    $key = $default;
                    $default = null;

                    if (! array_key_exists($key, $data)) {
                        throw new CascadeDataNotFoundException($key);
                    }
                }

                return [$key => Arr::get($data, $key, $default)];
            })
            ->all();
    }
}
