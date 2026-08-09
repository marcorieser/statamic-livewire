<?php

declare(strict_types=1);

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use MarcoRieser\Livewire\ServiceProvider;
use MarcoRieser\Livewire\Tests\TestCase;
use Statamic\Facades\Parse;
use Symfony\Component\HttpFoundation\Response;

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

/**
 * Post a Livewire update request for the component rendered in the given
 * html, the way Livewire's JavaScript would.
 *
 * @param  array<string, mixed>  $call
 * @return TestResponse<Response>
 */
function postLivewireUpdate(TestCase $testCase, string $html, array $call): TestResponse
{
    preg_match('/wire:snapshot="([^"]+)"/', $html, $matches);
    $snapshot = html_entity_decode($matches[1] ?? '', ENT_QUOTES);

    return $testCase
        ->withoutMiddleware(PreventRequestForgery::class)
        ->postJson(Livewire::getUpdateUri(), [
            'components' => [
                ['snapshot' => $snapshot, 'updates' => [], 'calls' => [$call]],
            ],
        ], ['X-Livewire' => '1']);
}
