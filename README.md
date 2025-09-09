# Statamic Livewire

A third-party [Laravel Livewire](https://laravel-livewire.com/) integration for Statamic. It aims to make it as easy as possible to use Livewire in Statamic.

## Table of Contents
* [Installation](#installation)
* [Upgrade](#upgrade)
* [Livewire documentation](#livewire-documentation)
* [Features](#features)
    + [Blade or Antlers? Yes, please!](#blade-or-antlers--yes--please-)
    + [Include components](#include-components)
    + [Passing Initial Parameters](#passing-initial-parameters)
    + [Keying Components](#keying-components)
    + [Manually including Livewire's frontend assets](#manually-including-livewire-s-frontend-assets)
    + [Manually bundling Livewire and Alpine](#manually-bundling-livewire-and-alpine)
    + [Static caching](#static-caching)
    + [`@script` and `@assets`](#--script--and---assets-)
    + [Computed Properties](#computed-properties)
    + [Multi-Site / Localization](#multi-site---localization)
    + [Lazy Components](#lazy-components)
    + [Paginating Data](#paginating-data)
    + [Synthesizers](#synthesizers)
    + [Entangle: Sharing State Between Livewire And Alpine](#entangle--sharing-state-between-livewire-and-alpine)
    + [This: Accessing the Livewire component](#this--accessing-the-livewire-component)
* [Other Statamic Livewire Packages](#other-statamic-livewire-packages)
* [Credits](#credits)
* [Requirements](#requirements)
- [Support](#support)
- [License](#license)

## Installation
Install the addon via composer:

```bash
composer require marcorieser/statamic-livewire
```

## Upgrade
Below is a list with specific upgrade instructions.

- [Upgrade from v4 to v5](docs/upgrade-4-to-5.md)
- [Upgrade from v3 to v4](docs/upgrade-3-to-4.md)
- [Addon ownership transfer (v3)](docs/addon-ownership-transfer.md)

## Livewire documentation
In general, all Livewire specific information can be found in the official [Livewire Docs](https://livewire.laravel.com/docs/quickstart).

## Features

### Blade or Antlers? Yes, please!
If creating a Livewire component, you need to render a template file

```php
namespace App\Http\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public function render()
    {
        return view('livewire.counter');
    }
}
```

Normally your template file would be a blade file, named `counter.blade.php`. Great, but what about Antlers?
Rename your template to `counter.antlers.html`, use Antlers syntax and do whatever you like. **No need to change** anything inside your component Controller. How cool is that?

More Information: (https://livewire.laravel.com/docs/components)

### Include components
You can create Livewire components as described in the [general documentation](https://livewire.laravel.com/docs/components).
To include your Livewire component in Antlers, you can use the `livewire` tag:

```antlers
{{ livewire:your-component-name }}
```

If you want to include a component from a dynamic variable, you can use the `livewire:component` tag:

```antlers
{{ livewire:component :name="variable" }}
```

### Passing Initial Parameters
You can pass data into a component by passing additional parameters:
```antlers
{{ livewire:your-component-name :contact="contact" }}
```

The [Official Livewire documentation](https://livewire.laravel.com/docs/components#rendering-components) provides more information.

### Keying Components
Livewire components are automatically keyed by default. If you want to manually key a component, you can use the `key` attribute.
```antlers
{{ contacts }}
    {{ livewire:your-component-name :key="id" }}
{{ /contacts }}
```
The [Official Livewire documentation](https://livewire.laravel.com/docs/components#adding-wirekey-to-foreach-loops) provides more information.

### Manually including Livewire's frontend assets
By default, Livewire injects the JavaScript and CSS assets it needs into each page that includes a Livewire component.
If you want more control over this behavior, you can [manually include the assets](https://livewire.laravel.com/docs/installation#manually-including-livewires-frontend-assets) on a page using the following Antlers tags:

```antlers
<html>
    <head>
        {{ livewire:styles }}
    </head>
    <body>

        {{ livewire:scripts }}
    </body>
</html>
```

### Manually bundling Livewire and Alpine
If you need to include some custom Alpine plugins, you need to [manually bundle the Livewire and Alpine assets](https://livewire.laravel.com/docs/installation#manually-bundling-livewire-and-alpine) and disable the automatic injection by using the following Antlers tag.
Remember to include the Livewire styles as well.

```antlers
<html>
    <head>
        {{ livewire:styles }}
    </head>
    <body>

        {{ livewire:scriptConfig }}
    </body>
</html>
```

### Static caching
This addon adds an `AssetsReplacer` class to make Livewire compatible with half and full static caching. You may customize the replacers in the config of this addon:

```php
'replacers' => [
    \MarcoRieser\Livewire\Replacers\AssetsReplacer::class,
],
```

If you are using full measure static caching, and you're manually bundling Livewire and Alpine as per the instructions above, you need to make sure to only start Livewire once the CSRF token has been replaced.

```js
if (window.livewireScriptConfig?.csrf === 'STATAMIC_CSRF_TOKEN') {
    document.addEventListener('statamic:nocache.replaced', () => Livewire.start());
} else {
    Livewire.start();
}
```

### `@script` and `@assets`
Antlers versions of [@script](https://livewire.laravel.com/docs/javascript#executing-scripts) and [@assets](https://livewire.laravel.com/docs/javascript#loading-assets) are provided:

```antlers
<body>
    {{ livewire:script }}
	<script>console.log('hello')</script>
    {{ /livewire:script }}
</body>
```

```antlers
<body>
    {{ livewire:assets }}
	<script src="some-javascript-library.js"></script>
    {{ /livewire:assets }}
</body>
```

### Computed Properties
When using Antlers, the computed properties are loaded automatically and only resolve when accessed. 
Simply access them as you would access a regular variable in the cascade.
Read more about [Computed Properties in the Livewire Docs](https://livewire.laravel.com/docs/computed-properties).

```php
#[Computed]
public function entries() {
    return Entry::all();
}
```
```antlers
{{ entries }}
    {{ title }}
{{ /entries }}
```

### Multi-Site / Localization
By default, your current site is persisted between Livewire requests automatically.  
In case you want to implement your own logic, you can disable `localization` in your published `config/statamic-livewire.php` config.

### Lazy Components
Livewire allows you to [lazy load components](https://livewire.laravel.com/docs/lazy) that would otherwise slow down the initial page load. For this you can simply pass `lazy="true"` as argument to your component tag.

```antlers
{{ livewire:your-component-name :contact="contact" lazy="true" }}
```

### Paginating Data
You can paginate results by using the WithPagination trait.

#### Blade
To use pagination with Blade, please use the `Livewire\WithPagination` namespace for your trait as described in the [Livewire docs](https://livewire.laravel.com/docs/pagination#basic-usage).

### Antlers
Pagination with Antlers does work similarly. Make sure to use the `MarcoRieser\Livewire\WithPagination` namespace for your trait if working with Antlers.

In your Livewire component view:
```antlers
{{ entries }}
    ...
{{ /entries }}

{{ links }}
```

```php
use MarcoRieser\Livewire\WithPagination;

class ShowArticles extends Component
{
    use WithPagination;

    protected function entries()
    {
        $entries = Entry::query()
            ->where('collection', 'articles')
            ->paginate(3);

        return $this->withPagination('entries', $entries);
    }

    public function render()
    {
        return view('livewire.blog-entries', $this->entries());
    }
}
```

### Synthesizers
You can use the built-in Synthesizers to make your Livewire components aware of Statamic specific data types.

```php
use Statamic\Entries\Entry;

class Foo extends Component
{
    public Entry $entries;
}
```

Currently, the following types are supported:
- `Statamic\Entries\EntryCollection`;
- `Statamic\Entries\Entry`;
- `Statamic\Fields\Field`;
- `Statamic\Fields\Fieldtype`;
- `Statamic\Fields\Value`;

To make it work, you need to enable that feature first.

1. Run `php artisan vendor:publish`
2. Select `statamic-livewire` in the list
3. Enable synthesizers

#### Augmentation
By default, the Synthesizers augment the data before it gets passed into the antlers view. You can disable this by setting `synthesizers.augmentation` to `false` in your published `config/statamic-livewire.php` config.

### Entangle: Sharing State Between Livewire And Alpine
It's worth mentioning that, since Livewire v3 now builds on top of Alpine, the `@entangle` directive is not documented anymore. Instead, it's possible to entangle the data via [the `$wire` object](https://livewire.laravel.com/docs/javascript#the-wire-object).
```antlers
<div x-data="{ open: $wire.entangle('showDropdown', true) }">
```

In case you want to share state between Livewire and Alpine, there is a Blade directive called `@entangle`. To be usable with Antlers, the addon provides a dedicated tag:
```antlers
<div x-data="{ open: {{ livewire:entangle property='showDropdown' modifier='live' }} }">
```

### This: Accessing the Livewire component
It's worth mentioning that, since Livewire v3 now builds on top of Alpine, the `@this` directive is not used widely anymore. Instead, it's possible to [access and manipulate the state directly via JavaScript](https://livewire.laravel.com/docs/properties#accessing-properties-from-javascript) / [the `$wire` object](https://livewire.laravel.com/docs/javascript#the-wire-object).
```antlers
<script>
    document.addEventListener('livewire:initialized', function () {
        // `{{ livewire:this }}` returns the instance of the current component
        {{ livewire:this }}.set('name', 'Jack')
    })
</script>
```

You can access and perform actions on the Livewire component like this:

```antlers
<script>
    document.addEventListener('livewire:initialized', function () {
        // With Antlers
        {{ livewire:this set="('name', 'Jack')" }}

        // With Blade
        @this.set('name', 'Jack')
    })
</script>
```

## Other Statamic Livewire Packages
If using Livewire, those packages might be interesting for you as well:
- [Livewire Forms](https://statamic.com/addons/aerni/livewire-forms)
- [Livewire Filters](https://statamic.com/addons/reach/statamic-livewire-filters)
- [Antlers Components](https://statamic.com/addons/stillat/antlers-components)
- [Live Search](https://statamic.com/addons/marcorieser/live-search)

Did I miss a link? Let me know!

## Credits

Thanks to:
- [Jonas Siewertsen](https://jonassiewertsen.com/) for building the addon and give me the permission to take it over
- [Caleb](https://github.com/calebporzio) and the community for building [Livewire](https://laravel-livewire.com/)
- [Austenc](https://github.com/austenc) for the Statamic marketplace preview image

## Requirements
- PHP 8.2
- Laravel 11, 12
- Statamic 5

# Support
I love to share with the community. Nevertheless, it does take a lot of work, time and effort.

[Sponsor me on GitHub](https://github.com/sponsors/marcorieser/) to support my work and the support for this addon.

# License
This plugin is published under the MIT license. Feel free to use it and remember to spread love.
