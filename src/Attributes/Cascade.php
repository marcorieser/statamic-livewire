<?php

namespace MarcoRieser\Livewire\Attributes;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Statamic\Exceptions\CascadeDataNotFoundException;
use Statamic\Facades\Cascade as CascadeFacade;

#[\Attribute]
class Cascade extends LivewireAttribute
{
    public function __construct(
        public array $keys = [],
        public string $contextAttribute = 'contextId',
    ) {}

    public function data(): array
    {
        if (! $data = CascadeFacade::toArray()) {
            // TODO[mr]: cache by permalink (02.04.2026 mr)
            $data = CascadeFacade::hydrate()->toArray();
        }

        if ($contextIdentifier = $this->resolveContextIdentifier()) {
            // TODO[mr]: cache by contextIdentifier (02.04.2026 mr)
            $path = $this->cascadePath(CascadeFacade::get('page'), $contextIdentifier);
            $data = array_merge($data, CascadeFacade::get($path)?->toArray() ?? []);
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

    protected function cascadePath($data, string $value, string $key = 'id', string $currentPath = ''): string
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        foreach ($data as $nestedKey => $nestedValue) {
            if ($nestedKey === $key && $nestedValue === $value) {
                return $currentPath;
            }

            if (is_array($nestedValue) || $nestedValue instanceof Arrayable) {
                $nestedPath = $currentPath === '' ? $nestedKey : $currentPath.'.'.$nestedKey;
                $result = $this->cascadePath($nestedValue, $value, $key, $nestedPath);

                if ($result !== '') {
                    return $result;
                }
            }
        }

        return '';
    }

    protected function resolveContextIdentifier()
    {
        return $this->getComponent()->getHtmlAttributes()[$this->contextAttribute] ?? null;
    }
}
