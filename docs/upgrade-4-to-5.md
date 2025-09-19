# Upgrade from v4 to v5

## Upgrade steps
1. Replace `"marcorieser/statamic-livewire": "^4.0"` with `"marcorieser/statamic-livewire": "^5.0"` in your `composer.json`
2. Remove the `RestoreCurrentSite` trait from your components
3. Remove the `{{ livewire:computed:... }}` tags in your views 
4. Remove the `DataFetcher` helper if you're using it (quite unlikely)

## In details

### Multisite / Localization
Livewire components are now aware of the current site they are in. This means localization is now supported out of the box and `Site::current()` acts just as you'd expect it to.

Therefore, the deprecated `RestoreCurrentSite` trait is removed, and you need to remove it from your components.

### Computed Properties
Computed properties are available in your Antlers views automatically. You can use the property name like any other variable.

For example `{{ livewire:computed:entries }}` is now `{{ entries }}`.

Additionally, the `DataFetcher` helper is removed since it was only used for accessing computed properties behind the scenes. 

### Synthesizers
The synthesizers are now not experimental anymore. But not all types are supported yet.
