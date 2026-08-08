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

A Laravel Livewire integration for Statamic.

## Installation

You can install the package via Composer:

```bash
composer require marcorieser/statamic-livewire
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="statamic-livewire"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="statamic-livewire-config"
```

### Publishing and Running the Migrations

```bash
php artisan vendor:publish --tag="statamic-livewire-migrations"
php artisan migrate
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="statamic-livewire-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="statamic-livewire-lang"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="statamic-livewire-assets"
```

## Usage

<!-- Add a basic usage example here. -->

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
