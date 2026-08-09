<div align="center">
    <h1>Statamic Livewire</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/marcorieser/statamic-livewire"><img src="https://img.shields.io/packagist/v/marcorieser/statamic-livewire.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/marcorieser/statamic-livewire"><img src="https://img.shields.io/packagist/php-v/marcorieser/statamic-livewire.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/marcorieser/statamic-livewire"><img src="https://badge.laravel.cloud/badge/marcorieser/statamic-livewire?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/marcorieser/statamic-livewire/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/marcorieser/statamic-livewire/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/marcorieser/statamic-livewire"><img src="https://img.shields.io/packagist/dt/marcorieser/statamic-livewire.svg?style=flat-square" alt="Total Downloads"></a>
</p>

A Laravel Livewire integration for Statamic. Use [Livewire](https://livewire.laravel.com) components in your [Antlers](https://statamic.dev/antlers) templates — including Blade or Antlers component views, Statamic-aware property synthesizers, multi-site support, and static caching compatibility.

> [!NOTE]
> Version 6 is a rewrite targeting Livewire 4 and Statamic 6 exclusively. For Livewire 3 or Statamic 5, use [version 5](https://github.com/marcorieser/statamic-livewire/tree/5.x).

## Requirements

- PHP 8.4+
- Laravel 13+
- Statamic 6+
- Livewire 4+

## Installation

You can install the package via Composer:

```bash
composer require marcorieser/statamic-livewire
```

Optionally, publish the configuration file:

```bash
php artisan vendor:publish --tag="statamic-livewire-config"
```

## Usage

### Mounting components in Antlers

Use the `livewire` tag (aliases: `lw`, `wire`) to render a Livewire component in any Antlers template:

```antlers
{{ livewire:your-component-name }}
```

Pass parameters like on any other tag — bound parameters (`:param`) resolve from the Antlers context:

```antlers
{{ livewire:counter label="Clicks" :count="entry_count" }}
```

To resolve the component name dynamically, use the `component` method with a `name` parameter:

```antlers
{{ livewire:component :name="component_name" }}
```

When rendering components in a loop, give each instance a [key](https://livewire.laravel.com/docs/components#adding-wirekey-to-foreach-loops):

```antlers
{{ items }}
    {{ livewire:item-card :item="id" :key="id" }}
{{ /items }}
```

### Antlers component views

Class components can render an Antlers view instead of a Blade view — reference the view by name, without the `.antlers.html` extension:

```php
class Counter extends Component
{
    public int $count = 0;

    public function render(): View
    {
        return view('livewire.counter'); // resources/views/livewire/counter.antlers.html
    }
}
```

Public properties are available as Antlers variables:

```antlers
<div>
    <span>{{ count }}</span>
    <button wire:click="increment">Increment</button>
</div>
```

Livewire's own component formats — class components with Blade views, [single-file components, and multi-file components](https://livewire.laravel.com/docs/components) — all work with the mount tag as well. Only single-file and multi-file component templates themselves must be Blade, since they run through Livewire's compiler.

### Including the assets

Livewire's assets are injected automatically. To control their placement — or when [auto-injection is disabled](https://livewire.laravel.com/docs/installation#manually-bundling-livewire-and-alpine) — use the Antlers equivalents of Livewire's Blade directives:

```antlers
<html>
<head>
    {{ livewire:styles }}
</head>
<body>
    {{ livewire:counter }}
    {{ livewire:scripts }}
</body>
</html>
```

When bundling Livewire manually, output the script configuration instead:

```antlers
{{ livewire:scriptConfig }}
```

### Custom assets and scripts

The Antlers equivalents of Livewire's [`@assets` and `@script` directives](https://livewire.laravel.com/docs/javascript):

```antlers
{{ livewire:assets }}
    <script src="https://cdn.example.com/chart.js" defer></script>
{{ /livewire:assets }}

{{ livewire:script }}
    <script>
        console.log('Component initialized');
    </script>
{{ /livewire:script }}
```

`{{ livewire:assets }}` loads an asset once per page, no matter how many components use it, and also works outside of component views. `{{ livewire:script }}` runs a script when its component initializes and must be used inside a component view.

### Computed properties

[Computed properties](https://livewire.laravel.com/docs/computed-properties) are available as variables in Antlers component views — no need to call them like methods:

```php
class ShowPost extends Component
{
    #[Computed]
    public function post(): array
    {
        return Entry::find($this->postId)->toAugmentedArray();
    }
}
```

```antlers
<div>
    {{ post:title }}
</div>
```

Each computed property is resolved lazily: it only executes when the view actually uses it.

### Cascade data

Statamic's [cascade](https://statamic.dev/cascade) is not available in Livewire component views by default. Add the `#[Cascade]` attribute to a component to expose it to the component's Antlers view:

```php
use MarcoRieser\Livewire\Attributes\Cascade;

#[Cascade]
class ShowArticle extends Component
{
    //
}
```

```antlers
<div>
    {{ title }} on {{ site:name }}
</div>
```

To keep the view scope clean and make the component's dependencies explicit, select only the keys you need. String keys define a fallback for when the key is absent from the cascade; selecting a missing key without a fallback throws an exception:

```php
#[Cascade(['title', 'author' => 'Anonymous'])]
```

The package also keeps the cascade consistent across component updates: on Livewire requests, the cascade is rebuilt as if the original page URL was requested — including the site, request, and page content.

### Multi-site

On [multi-site](https://statamic.dev/multi-site) installations, the current site and its locale are resolved from the original page URL on Livewire update requests — components keep rendering in the site context of the page they live on. This is enabled by default and can be turned off in the config:

```php
'localization' => false,
```

### Static caching

Livewire works on [statically cached](https://statamic.dev/static-caching) pages out of the box. Two replacers are registered automatically:

- Livewire's assets (and any `{{ livewire:assets }}` content) are baked into cached responses, since asset injection normally happens after the response is prepared for caching.
- Livewire's back/forward-cache protection headers (`Cache-Control: no-store`, …) are restored on cached pages containing components, so browsers don't restore stale component snapshots when navigating back.

The replacers can be adjusted in the config:

```php
'replacers' => [
    \MarcoRieser\Livewire\Replacers\AssetsReplacer::class,
    \MarcoRieser\Livewire\Replacers\DisableBackButtonCacheReplacer::class,
],
```

### Passing Statamic data to components

Don't store Statamic objects like entries in component properties — they would be serialized into the Livewire payload on every request, and the component would work with stale, client-round-tripped data. Pass an id instead, and resolve the object in a [computed property](#computed-properties):

```antlers
{{ livewire:show-article :article="id" }}
```

```php
class ShowArticle extends Component
{
    public string $article;

    #[Computed]
    public function entry(): ?Entry
    {
        return Entry::find($this->article);
    }
}
```

```antlers
<div>
    <h1>{{ entry:title }}</h1>
    {{ entry:content }}
</div>
```

This keeps the payload small and guarantees fresh content on every update — the computed property is only fetched when the view uses it, and only once per request.

> [!NOTE]
> Version 5's opt-in property synthesizers were removed in favor of this pattern.

<!-- Sections are added as features land: pagination, slots, islands. -->

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Statamic Livewire! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Marco Rieser](https://github.com/marcorieser)
- [All Contributors](../../contributors)

## License

Statamic Livewire is open-sourced software licensed under the [MIT license](LICENSE.md).
