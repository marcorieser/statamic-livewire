<?php

namespace MarcoRieser\Livewire\Tests\Feature;

use Illuminate\Http\Response;
use Livewire\Features\SupportAutoInjectedAssets\SupportAutoInjectedAssets;
use Livewire\Features\SupportScriptsAndAssets\SupportScriptsAndAssets;
use Livewire\Mechanisms\FrontendAssets\FrontendAssets;
use MarcoRieser\Livewire\Replacers\AssetsReplacer;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class StaticCachingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['statamic.static_caching.strategy' => 'half']);

        SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = false;
        SupportAutoInjectedAssets::$forceAssetInjection = false;
        SupportScriptsAndAssets::$renderedAssets = [];
    }

    #[Test]
    public function registers_the_replacer_for_static_caching()
    {
        $this->assertContains(AssetsReplacer::class, config('statamic.static_caching.replacers'));
    }

    #[Test]
    public function bakes_the_livewire_assets_into_responses_prepared_for_caching()
    {
        SupportAutoInjectedAssets::$hasRenderedAComponentThisRequest = true;

        $response = new Response('<html><head></head><body><div>content</div></body></html>');

        (new AssetsReplacer)->prepareResponseToCache($response, $response);

        $content = (string) $response->getContent();

        $this->assertStringContainsString('<style', $content);
        $this->assertStringContainsString('livewire', $content);
        $this->assertFalse(app(FrontendAssets::class)->hasRenderedStyles);
        $this->assertFalse(app(FrontendAssets::class)->hasRenderedScripts);
    }

    #[Test]
    public function leaves_responses_without_livewire_untouched_when_preparing_for_caching()
    {
        $content = '<html><head></head><body><div>content</div></body></html>';
        $response = new Response($content);

        (new AssetsReplacer)->prepareResponseToCache($response, $response);

        $this->assertSame($content, $response->getContent());
    }

    #[Test]
    public function ignores_empty_responses_when_preparing_for_caching()
    {
        $response = new Response('');

        (new AssetsReplacer)->prepareResponseToCache($response, $response);

        $this->assertSame('', $response->getContent());
    }

    #[Test]
    public function marks_livewire_scripts_as_already_rendered_when_a_cache_hit_already_contains_them()
    {
        $content = '<html><head></head><body>'
            .'<script src="/livewire/livewire.min.js" data-module-url="/livewire" data-update-uri="/livewire/update"></script>'
            .'</body></html>';

        (new AssetsReplacer)->replaceInCachedResponse(new Response($content));

        $this->assertTrue(app(FrontendAssets::class)->hasRenderedScripts);
    }

    #[Test]
    public function marks_livewire_styles_as_already_rendered_when_a_cache_hit_already_contains_them()
    {
        $content = '<html><head><!-- Livewire Styles --><style>[wire\:loading]{}</style></head><body></body></html>';

        (new AssetsReplacer)->replaceInCachedResponse(new Response($content));

        $this->assertTrue(app(FrontendAssets::class)->hasRenderedStyles);
    }

    #[Test]
    public function leaves_the_render_flags_untouched_on_cache_hits_without_baked_in_livewire_assets()
    {
        (new AssetsReplacer)->replaceInCachedResponse(new Response('<html><body><div wire:id="abc">component</div></body></html>'));

        $this->assertFalse(app(FrontendAssets::class)->hasRenderedScripts);
        $this->assertFalse(app(FrontendAssets::class)->hasRenderedStyles);
    }

    #[Test]
    public function ignores_empty_responses_on_cache_hits()
    {
        (new AssetsReplacer)->replaceInCachedResponse(new Response(''));

        $this->assertFalse(app(FrontendAssets::class)->hasRenderedScripts);
        $this->assertFalse(app(FrontendAssets::class)->hasRenderedStyles);
    }
}
