---
name: statamic-livewire-development
description: >
  Use Livewire 4 components in Statamic 6 Antlers templates: mount tags,
  Antlers component views, slots, islands, cascade data, multi-site and
  static caching support.
license: MIT
metadata:
  author: Marco Rieser
---

# Statamic Livewire

Use this skill when a Statamic 6 site needs Livewire components in Antlers templates via `marcorieser/statamic-livewire` (requires PHP 8.4+, Laravel 13+, Statamic 6.24+, Livewire 4.2+).

## Primary Goal

- apply the package's Antlers tags and attributes in the smallest correct way, mirroring Livewire's Blade features

## Workflow

### 1. Install

```bash
composer require marcorieser/statamic-livewire
php artisan vendor:publish --tag="statamic-livewire-config"   # optional
```

Config keys: `localization` (resolve site/locale from the original URL on update requests, default `true`) and `replacers` (static caching support, registered automatically).

### 2. Mount components in Antlers

```antlers
{{ livewire:counter }}                            {{# by name; aliases: lw, wire #}}
{{ livewire:counter label="Clicks" :count="n" }}  {{# params; bound params resolve from context #}}
{{ livewire:component :name="component_name" }}   {{# dynamic component name #}}
{{ livewire:item-card :item="id" :key="id" }}     {{# key in loops #}}
{{ livewire:revenue lazy="true" }}                {{# lazy load; defer="true" loads after page load #}}
```

Lazy-loaded components define their loading state with a `placeholder(): string` method on the component class.

### 3. Component views

Class components may return Antlers views — reference them without the `.antlers.html` extension; public and `#[Computed]` properties become Antlers variables:

```php
public function render(): View
{
    return view('livewire.counter'); // resources/views/livewire/counter.antlers.html
}
```

Single-file and multi-file Livewire components mount fine, but their templates must be Blade.

### 4. Assets

```antlers
{{ livewire:styles }}        {{# manual asset placement, head #}}
{{ livewire:scripts }}       {{# body #}}
{{ livewire:scriptConfig }}  {{# when bundling manually #}}

{{ livewire:assets }}<script src="..." defer></script>{{ /livewire:assets }}  {{# once per page, also outside components #}}
{{ livewire:script }}<script>...</script>{{ /livewire:script }}               {{# runs on component init, inside component views only #}}
```

### 5. Statamic data

Pass ids, not objects — resolve in computed properties (there are no synthesizers):

```php
public string $article;

#[Computed]
public function entry(): ?Entry
{
    return Entry::find($this->article);
}
```

Expose the Statamic cascade with the `#[Cascade]` attribute (all keys, or selected keys with fallbacks):

```php
use MarcoRieser\Livewire\Attributes\Cascade;

#[Cascade(['title', 'author' => 'Anonymous'])]
class ShowArticle extends Component {}
```

### 6. Slots

```antlers
{{ livewire:card }}
    {{ livewire:slot name="header" }}<h1>{{ title }}</h1>{{ /livewire:slot }}
    <p>Default slot content.</p>
{{ /livewire:card }}
```

In the component's Antlers view: `{{ slots:default }}`, `{{ slots:header }}`, conditionally via `{{ if slots:header }}`.

### 7. Islands

```antlers
{{ livewire:island name="stats" lazy="true" }}
    {{ livewire:placeholder }}<span>Loading…</span>{{ /livewire:placeholder }}
    <span>{{ count }}</span>
{{ /livewire:island }}
```

Modes: `lazy`, `defer`, `always`, `skip`. Islands need a unique `name` per component; in loops make it dynamic (`name="item-{id}"`) and pass loop values as parameters — they are captured and persist across island updates (keep them JSON-serializable).

### 8. Pagination in Antlers views

```php
use MarcoRieser\Livewire\WithPagination;

return view('livewire.articles')->with($this->withPagination('entries', $paginator));
```

```antlers
{{ entries }} ... {{ /entries }}
{{ links }}
```

For multiple paginators pass `linksKey: 'articles_links'`.

## Rules, References, and Templates

- Multi-site and static caching (asset re-injection, back/forward-cache headers) work automatically; `localization` config turns site resolution off.
- Full docs: `README.md`; migration from v5: `UPGRADE.md`.

## Examples

- Mount a counter component on an entry template with `{{ livewire:counter :initial="view_count" }}` and render its Antlers view from `resources/views/livewire/counter.antlers.html`.
- Build an articles list with `WithPagination`, an `#[Cascade(['title'])]` heading, and a lazy `{{ livewire:island }}` for slow stats.

## Anti-patterns

- Storing Statamic objects (entries, fields, values) in component properties — pass ids and use `#[Computed]`.
- Using `{{ livewire:script }}` or `{{ livewire:island }}` outside of a component view (both throw).
- Using Blade's `$slot` / `@placeholder` syntax in Antlers views — use `{{ slots:default }}` and `{{ livewire:placeholder }}`.
- Expecting `{{ livewire:this }}` or `{{ livewire:entangle }}` — removed in v6, use `$wire` in script blocks.
