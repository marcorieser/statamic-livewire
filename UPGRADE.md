# Upgrade Guide

## From v5 to v6

Version 6 is a rewrite targeting Livewire 4 and Statamic 6 exclusively. Most template-facing APIs are unchanged — the mount tag, asset tags, computed properties, cascade, multi-site and static caching behave as before. Review the following changes:

### Requirements

The version floors moved. Upgrade your application first:

| Dependency | v5 | v6 |
| --- | --- | --- |
| PHP | 8.2+ | 8.4+ |
| Laravel | 11+ | 13+ |
| Statamic | 5.73+ | 6.24+ |
| Livewire | 3.6+ | 4.2+ |

When upgrading from Livewire 3, also follow [Livewire's own upgrade guide](https://livewire.laravel.com/docs/upgrading) (e.g. `layout` → `component_layout` config, changed defaults).

### Removed: `{{ livewire:this }}` and `{{ livewire:entangle }}`

Both tags were deprecated in v5 and are gone. Use Livewire's own JavaScript utilities instead — `$wire` inside `{{ livewire:script }}` blocks and `$wire.entangle()` for Alpine bindings.

### Removed: the `Livewire` facade

The package no longer ships its own `Livewire` facade (it collided with Livewire's). Use `\Livewire\Livewire` directly.

### Removed: property synthesizers

The opt-in synthesizers (`slw_entry`, `slw_entryco`, `slw_field`, `slw_fieldtype`, `slw_value`) and their config keys (`synthesizers.enabled`, `synthesizers.classes`, `synthesizers.augmentation`) were removed. They serialized full Statamic objects into the Livewire payload and rebuilt them from client data.

Instead, pass ids and resolve objects in computed properties — see [Passing Statamic data to components](README.md#passing-statamic-data-to-components):

```php
// Before
public Entry $entry;

// After
public string $entry;

#[Computed]
public function entry(): ?Entry
{
    return Entry::find($this->entry);
}
```

### Changed: tag misuse now throws

Instead of silently rendering nothing, the tags now fail loudly:

- `{{ livewire }}` or `{{ livewire:component }}` without a component name throws an `InvalidArgumentException`.
- `{{ livewire:script }}` outside of a component view throws a `RuntimeException`.

The tag methods `slot`, `island` and `placeholder` are reserved: components with those names can no longer be mounted via `{{ livewire:slot }}` etc. — use `{{ livewire component="slot" }}` instead (or rename the component).

### Changed: config file

The config was reduced to `localization` and `replacers`. Republish it if you had customized it:

```bash
php artisan vendor:publish --tag="statamic-livewire-config" --force
```

The `replacers` default now includes `DisableBackButtonCacheReplacer`, which restores Livewire's back/forward-cache protection headers on statically cached pages ([#30](https://github.com/marcorieser/statamic-livewire/issues/30)).

### New features

No changes required, but worth adopting:

- [Slots](README.md#slots): pass content into components with tag pairs, including named slots.
- [Islands](README.md#islands): isolated regions that update independently, incl. lazy loading and placeholders.
- [Lazy components](README.md#lazy-loading-components): `lazy="true"` / `defer="true"` on the mount tag.
- `{{ livewire:assets }}` now also works outside of component views.
