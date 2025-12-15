<?php

namespace MarcoRieser\Livewire\Attributes;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;
use Livewire\Features\SupportAttributes\Attribute as LivewireAttribute;
use Livewire\Livewire;
use Statamic\Exceptions\CascadeDataNotFoundException;
use Statamic\Facades\Cascade as CascadeFacade;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;
use Statamic\Support\Arr;

#[\Attribute]
class Cascade extends LivewireAttribute
{
    public function __construct(
        public array $keys = [],
        public bool $content = true,
    ) {}

    public function getCascadeData(): array
    {
        if (! $data = CascadeFacade::toArray()) {
            $this->hydrateSite();
            $this->hydrateRequest();
            $this->hydrateContent();

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

    protected function hydrateSite(): void
    {
        CascadeFacade::withSite(Site::current());
    }

    protected function hydrateRequest(): void
    {
        CascadeFacade::withRequest(Request::create(uri: Livewire::originalUrl(), method: Livewire::originalMethod()));
    }

    protected function hydrateContent(): void
    {
        if (! $this->content) {
            return;
        }

        $url = Str::of(Livewire::originalUrl())
            ->after(Site::current()->absoluteUrl())
            ->start('/');

        if (! ($entry = Entry::findByUri($url))) {
            return;
        }

        CascadeFacade::withContent($entry);
    }
}
