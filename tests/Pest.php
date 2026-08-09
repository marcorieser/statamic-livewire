<?php

declare(strict_types=1);

use MarcoRieser\Livewire\ServiceProvider;
use MarcoRieser\Livewire\Tests\TestCase;
use Statamic\Facades\Parse;

pest()->extend(TestCase::class)->in(__DIR__);

function addonProvider(): ServiceProvider
{
    $provider = app()->getProvider(ServiceProvider::class);

    if (! $provider instanceof ServiceProvider) {
        throw new RuntimeException('The addon service provider is not registered.');
    }

    return $provider;
}

/**
 * Render an Antlers template the way Statamic renders views: as trusted
 * template code. Untrusted parsing (the default) skips tag execution.
 *
 * @param  array<string, mixed>  $context
 */
function antlers(string $template, array $context = []): string
{
    return (string) Parse::template($template, $context, [], true);
}
