<?php

declare(strict_types=1);

use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Livewire;
use MarcoRieser\Livewire\Tests\Fixtures\AssetsUser;

beforeEach(function (): void {
    Livewire::component('assets-user', AssetsUser::class);

    SupportScriptsAndAssets::$alreadyRunAssetKeys = [];
    SupportScriptsAndAssets::$renderedAssets = [];
    SupportScriptsAndAssets::$nonLivewireAssets = [];
});

it('outputs the livewire styles', function (): void {
    expect(antlers('{{ livewire:styles }}'))->toContain('<style');
});

it('outputs the livewire scripts', function (): void {
    expect(antlers('{{ livewire:scripts }}'))->toContain('<script')->toContain('livewire');
});

it('outputs the livewire script config', function (string $template): void {
    expect(antlers($template))->toContain('livewireScriptConfig');
})->with([
    'camel case' => '{{ livewire:scriptConfig }}',
    'snake case' => '{{ livewire:script_config }}',
]);

it('outputs styles and scripts only once per request', function (): void {
    expect(substr_count(antlers('{{ livewire:styles }}{{ livewire:styles }}'), '<style'))->toBe(1)
        ->and(substr_count(antlers('{{ livewire:scripts }}{{ livewire:scripts }}'), '<script'))->toBe(1);
});

it('registers assets used inside a component view', function (): void {
    $html = antlers('{{ livewire:assets-user }}');

    expect($html)
        ->toContain('assets user')
        ->toContain('&quot;assets&quot;:[&quot;');
});

it('registers an asset only once per request', function (): void {
    $html = antlers('{{ livewire:assets-user }}{{ livewire:assets-user }}');

    expect(substr_count($html, '&quot;assets&quot;:[&quot;'))->toBe(1);
});

it('registers assets used outside of a component', function (): void {
    expect(antlers('{{ livewire:assets }}<link rel="stylesheet" href="/custom.css">{{ /livewire:assets }}'))->toBeEmpty()
        ->and(SupportScriptsAndAssets::$nonLivewireAssets)->toHaveCount(1)
        ->and(array_values(SupportScriptsAndAssets::$nonLivewireAssets)[0])->toContain('/custom.css');
});

it('registers scripts used inside a component view', function (): void {
    expect(antlers('{{ livewire:assets-user }}'))->toContain('&quot;scripts&quot;:[&quot;');
});

it('throws when the script tag is used outside of a component', function (): void {
    antlers('{{ livewire:script }}<script>console.log("nope")</script>{{ /livewire:script }}');
})->throws(RuntimeException::class, 'The {{ livewire:script }} tag must be used inside a Livewire component view.');
