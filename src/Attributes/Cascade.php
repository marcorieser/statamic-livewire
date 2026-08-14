<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Attributes;

use Attribute;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Statamic\Exceptions\CascadeDataNotFoundException;
use Statamic\Facades\Cascade as CascadeFacade;

#[Attribute]
class Cascade extends LivewireAttribute
{
    /**
     * @param  array<int|string, mixed>  $keys  Cascade keys to expose. An empty
     *                                          array exposes the whole cascade.
     *                                          String keys map to a default value
     *                                          for when the cascade key is absent.
     */
    public function __construct(public array $keys = []) {}

    /**
     * @return array<string, mixed>
     */
    public function getCascadeData(): array
    {
        if (($data = CascadeFacade::toArray()) === []) {
            $data = CascadeFacade::hydrate()->toArray();
        }

        if ($this->keys === []) {
            return $data;
        }

        return collect($this->keys)
            ->mapWithKeys(function (mixed $default, int|string $key) use ($data): array {
                if (is_int($key)) {
                    if (! is_string($default)) {
                        throw new InvalidArgumentException('Cascade keys must be strings.');
                    }

                    $key = $default;
                    $default = null;

                    if (! Arr::has($data, $key)) {
                        throw new CascadeDataNotFoundException($key);
                    }
                }

                return [$key => Arr::get($data, $key, $default)];
            })
            ->all();
    }
}
