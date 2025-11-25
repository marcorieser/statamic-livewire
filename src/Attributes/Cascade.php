<?php

namespace MarcoRieser\Livewire\Attributes;

use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Statamic\Exceptions\CascadeDataNotFoundException;
use Statamic\Facades\Cascade as CascadeFacade;
use Statamic\Support\Arr;

#[\Attribute]
class Cascade extends LivewireAttribute
{
    public function __construct(
        public $keys = null,
    ) {}

    public function getCascadeData(): array
    {
        if (! $data = CascadeFacade::toArray()) {
            $data = CascadeFacade::hydrate()->toArray();
        }

        if (! isset($this->keys)) {
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
