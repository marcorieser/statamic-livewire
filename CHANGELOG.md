# Release Notes

## [Unreleased](https://github.com/marcorieser/statamic-livewire/compare/v6.0.0...6.x)

## [v6.0.0](https://github.com/marcorieser/statamic-livewire/compare/v5.3.1...v6.0.0) - 202x-xx-xx

Full rewrite targeting Livewire 4 and Statamic 6 exclusively. See the [upgrade guide](UPGRADE.md) for migrating from v5.

### Added

- Slots for Antlers views, including named slots (`{{ livewire:slot }}`)
- Islands for Antlers views (`{{ livewire:island }}`) with lazy, defer, always and skip modes, placeholders (`{{ livewire:placeholder }}`), captured scope, and loops via dynamic names
- Lazy and deferred component loading through the mount tag (`lazy="true"` / `defer="true"`)
- `{{ livewire:assets }}` works outside of component views
- Back/forward-cache protection headers are restored on statically cached pages ([#30](https://github.com/marcorieser/statamic-livewire/issues/30))
- Optional `linksKey` parameter on `withPagination()` for multiple paginators per component

### Changed

- Requires PHP 8.4+, Laravel 13+, Statamic 6.24+ and Livewire 4.2+
- Tag misuse throws exceptions instead of rendering nothing
- The cascade no longer crashes on update requests for `Route::statamic()` pages without content ([#26](https://github.com/marcorieser/statamic-livewire/issues/26))

### Removed

- Deprecated `{{ livewire:this }}` and `{{ livewire:entangle }}` tags
- The package's `Livewire` facade
- Property synthesizers, in favor of the id + computed property pattern

## v5 and earlier

See the [releases on GitHub](https://github.com/marcorieser/statamic-livewire/releases).
