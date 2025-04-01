# Statamic Livewire
<!-- statamic:hide -->
![Statamic 4.0+](https://img.shields.io/badge/Statamic-4.0+-FF269E?style=for-the-badge&link=https://statamic.com)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/marcorieser/statamic-livewire.svg?style=for-the-badge)](https://packagist.org/packages/marcorieser/statamic-livewire)
<!-- /statamic:hide -->

## Description
A third-party [Laravel Livewire](https://laravel-livewire.com/) integration for Statamic.

It's as easy as it gets to get started with Livewire if using Statamic.

## Migrate from `jonassiewertsen/statamic-livewire`
### Without breaking changes (v3)
1. Replace `jonassiewertsen/statamic-livewire` with `marcorieser/statamic-livewire` in your `composer.json`
2. Run `composer update marcorieser/statamic-livewire`

### With breaking changes (v4)
1. Replace `"jonassiewertsen/statamic-livewire": "^3.0"` with `"marcorieser/statamic-livewire": "^4.0"` in your `composer.json`
2. Replace the namespace `Jonassiewertsen\Livewire` with `MarcoRieser\Livewire` in your code
3. Run `composer update marcorieser/statamic-livewire`

## Installation
Pull in the Livewire package with composer

```bash
composer require marcorieser/statamic-livewire
```

### Manually including Livewire's frontend assets
By default, Livewire injects the JavaScript and CSS assets it needs into each page that includes a Livewire component. If you want more control over this behavior, you can [manually include the assets](https://livewire.laravel.com/docs/installation#manually-including-livewires-frontend-assets) on a page using the following Antlers tags or Blade directives:

```html
<html>
    <head>
        <!-- If using Antlers -->
        {{ livewire:styles }}

        <!-- If using Blade -->
        @livewireStyles
    </head>
    <body>

        ...
        <!-- If using Antlers -->
        {{ livewire:scripts }}

        <!-- Blade -->
        @livewireScripts
    </body>
</html>
```

### Manually bundling Livewire and Alpine
If you need to include some custom Alpine plugins, you need to [manually bundle the Livewire and Alpine assets](https://livewire.laravel.com/docs/installation#manually-bundling-livewire-and-alpine) and disable the automatic injection by using the following Antlers tag or Blade directive. Do not forget to include the Livewire styles as well.

```html
<html>
    <head>
        <!-- If using Antlers -->
        {{ livewire:styles }}

        <!-- If using Blade -->
        @livewireStyles
    </head>
    <body>

        ...
        <!-- If using Antlers -->
        {{ livewire:scriptConfig }}

        <!-- Blade -->
        @livewireScriptConfig
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

If you are using full measure static caching and you're manually bundling Livewire and Alpine as per the instructions above, you need to make sure to only start Livewire once the CSRF token has been replaced.

```js
if (window.livewireScriptConfig?.csrf === 'STATAMIC_CSRF_TOKEN') {
    document.addEventListener('statamic:nocache.replaced', () => Livewire.start());
} else {
    Livewire.start();
}
```

## Upgrade

Make sure to read the Livewire upgrade guide, in case you're updating to `Statamic Livewire` 3, as there are breaking changes:
https://livewire.laravel.com/docs/upgrading

## General documentation
[Livewire Docs](https://livewire.laravel.com/docs/quickstart)

### Include components
You can create Livewire components as described in the [general documentation](https://livewire.laravel.com/docs/components).
To include your Livewire component:
```html
<body>
    <!-- If using Antlers -->
    {{ livewire:your-component-name }}

    <!-- If using Blade -->
    <livewire:your-component-name />
</body>
```

if you want to include a component from a dynamic variable you can use the `livewire:component` tag:

```html
<body>
    <!-- If using Antlers -->
    {{ livewire:component :name="variable" }}

    <!-- If using Blade -->
    <livewire:component name="$variable" />
</body>
```

### @script and @assets
Antlers versions of [@script](https://livewire.laravel.com/docs/javascript#executing-scripts) and [@assets](https://livewire.laravel.com/docs/javascript#loading-assets) are provided:

```html
<body>
    {{ livewire:script }}
	<script>console.log('hello')</script>
    {{ /livewire:script }}
</body>
```

```html
<body>
    {{ livewire:assets }}
	<script src="some-javascript-library.js"></script>
    {{ /livewire:assets }}
</body>
```

### Blade or Antlers? Both!
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
More Information: (https://livewire.laravel.com/docs/components)

Normally your template file would be a blade file, named `counter.blade.php`. Great, but what about Antlers?
Rename your template to `counter.antlers.html`, use Antlers syntax and do whatever you like. **No need to change** anything inside your component Controller. How cool is that?

### Passing Initial Parameters
You can pass data into a component by passing additional parameters
```html
<!-- If using Antlers -->
{{ livewire:your-component-name :contact="contact" }}

<!-- If using Blade -->
<livewire:your-component-name :contact="$contact">
```

To intercept with those parameters, mount them and store the data as public properties.

```php
use Livewire\Component;

class ShowContact extends Component
{
    public $name;
    public $email;

    public function mount($contact)
    {
        $this->name = $contact->name;
        $this->email = $contact->email;
    }

    ...
}
```

The [Official Livewire documentation](https://livewire.laravel.com/docs/components#rendering-components) provides more information.

### Keying Components
Livewire components are automatically keyed by default. If you want to manually key a component, you can use the `key` attribute.
```html
<!-- If using Antlers -->
{{ contacts }}
    {{ livewire:your-component-name :key="id" }}
{{ /contacts }}

<!-- If using Blade -->
@foreach ($contacts as $contact)
    <livewire:your-component-name :key="$contact->id" />
@endforeach
```
The [Official Livewire documentation](https://livewire.laravel.com/docs/components#adding-wirekey-to-foreach-loops) provides more information.

### Multi-Site / Localization
> There is an experimental approach to the trait (see below).

When using Livewire in a Multi-Site context, the current site gets lost between requests. There is a trait (`\MarcoRieser\Livewire\RestoreCurrentSite`) to solve that. Just include it in your component and use `Site::current()` as you normally do.
```php
class ShowArticles extends Component
{
    use \MarcoRieser\Livewire\RestoreCurrentSite;

    protected function entries()
    {
        return Entry::query()
            ->where('collection', 'articles')
            ->where('site', Site::current())
            ->get();
    }

    public function render()
    {
        return view('livewire.blog-entries', $this->entries());
    }
}
```

#### Automatic localization handling (experimental)
You can set `localization.enabled` to `true` in your published `config/statamic-livewire.php` config. This enables automatic localization handling, and you can omit the above-mentioned trait entirely. 

### Paginating Data
You can paginate results by using the WithPagination trait.

#### Blade
To use pagination with Blade, please use the `Livewire\WithPagination` namespace for your trait as described in the [Livewire docs](https://livewire.laravel.com/docs/pagination#basic-usage).

### Antlers
Pagination with Antlers does work similar. Make sure to use the `MarcoRieser\Livewire\WithPagination` namespace for your trait if working with Antlers.

In your Livewire component view:
```html
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

### EXPERIMENTAL: Statamic Support
As a little experiment, support for an Entry or EntryCollection has been added, so you can make an entry or a entry collection simply a public property and it just works.

Supported types:
- Statamic\Entries\EntryCollection;
- Statamic\Entries\Entry;

```php
namespace App\Livewire;

use Livewire\Component;
use Statamic\Entries\EntryCollection;
use Statamic\Entries\Entry;

class Foo extends Component
{
    public EntryCollection $entries;
    public Entry $entry;

    // normal livewire stuff
}
```

To make it work, you need to enable that feature first.

1. php artisan vendor:publish
2. Select statamic-livewire in the list
3. Enable synthesizers

### Entangle: Sharing State Between Livewire And Alpine
In case you want to share state between Livewire and Alpine, there is a Blade directive called `@entangle`. To be usable with Antlers, we do provide a dedicated tag:
```html
<!-- With Antlers -->
<div x-data="{ open: {{ livewire:entangle property='showDropdown' modifier='live' }} }">

<!-- With Blade -->
<div x-data="{ open: @entangle('showDropdown').live }">
```

It's worth mentioning that, since Livewire v3 now builds on top of Alpine, the `@entangle` directive is not documented anymore. Instead, it's possible to entangle the data via [the `$wire` object](https://livewire.laravel.com/docs/javascript#the-wire-object).
```html
<div x-data="{ open: $wire.entangle('showDropdown', true) }">
```
### This: Accessing the Livewire component
You can access and perform actions on the Livewire component like this:

```html
<script>
    document.addEventListener('livewire:initialized', function () {
        // With Antlers
        {{ livewire:this set="('name', 'Jack')" }}

        // With Blade
        @this.set('name', 'Jack')
    })
</script>
```
It's worth mentioning that, since Livewire v3 now builds on top of Alpine, the `@this` directive is not used widely anymore. Instead, it's possible to [access and manipulate the state directly via JavaScript](https://livewire.laravel.com/docs/properties#accessing-properties-from-javascript) / [the `$wire` object](https://livewire.laravel.com/docs/javascript#the-wire-object).
```html
<script>
    document.addEventListener('livewire:initialized', function () {
        // `{{ livewire:this }}` returns the instance of the current component
        {{ livewire:this }}.set('name', 'Jack')
    })
</script>
```
### Lazy Components
Livewire allows you to [lazy load components](https://livewire.laravel.com/docs/lazy) that would otherwise slow down the initial page load. For this you can simply pass `lazy="true"` as argument to your component tag.

```html
<!-- With Antlers -->
{{ livewire:your-component-name :contact="contact" lazy="true" }}
```

## Other Statamic Livewire Packages
If using Livewire, those packages might be interesting for you as well:
- [Statamic live search](https://github.com/jonassiewertsen/statamic-live-search)
- [Statamic Livewire Forms](https://github.com/aerni/statamic-livewire-forms)
- [Antlers Components](https://github.com/Stillat/antlers-components)

Did I miss a link? Let me know!

## Credits

Thanks to:
- [Jonas Siewertsen](https://jonassiewertsen.com/) for building the addon and give me the permission to take it over
- [Caleb](https://github.com/calebporzio) and the community for building [Livewire](https://laravel-livewire.com/)
- [Austenc](https://github.com/austenc) for the Statamic marketplace preview image

## Requirements
- PHP 8.1
- Laravel 10 or 11
- Statamic 4 or 5

# Support
I love to share with the community. Nevertheless, it does take a lot of work, time and effort.

[Sponsor me on GitHub](https://github.com/sponsors/marcorieser/) to support my work and the support for this addon.

# License
This plugin is published under the MIT license. Feel free to use it and remember to spread love.
