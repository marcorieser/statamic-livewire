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

<!-- Sections are added as features land: mount tag, asset tags, Antlers component views, computed properties, cascade, multi-site, static caching, synthesizers, pagination, slots, islands. -->

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
