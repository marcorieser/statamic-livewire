<?php

namespace MarcoRieser\Livewire\Tests\Features\Islands;

use Illuminate\Support\Facades\File;
use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use MarcoRieser\Livewire\Islands\IslandRenderer;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AntlersIslandsTest extends TestCase
{
    /**
     * The island cache in the testbench skeleton survives across PHPUnit
     * processes, so stale files from previous (filtered) runs would leak
     * into content-addressed tokens.
     */
    protected function setUp(): void
    {
        parent::setUp();

        File::deleteDirectory(app('livewire.compiler')->cacheManager->cacheDirectory.'/islands');
    }

    #[Test]
    public function island_content_is_rendered_with_antlers_on_mount()
    {
        $testable = $this->mountIslandComponent();

        $testable->assertSee('Outside the island');
        $testable->assertSee('Hello World!');
        $testable->assertSeeHtml('type=island|name=stats|token=antlers-');
    }

    #[Test]
    public function island_is_skipped_on_subsequent_renders()
    {
        $testable = $this->mountIslandComponent();

        $testable->call('$refresh');

        $testable->assertSee('Outside the island');
        $testable->assertDontSee('Hello World!');
        $testable->assertSeeHtml('mode=skip');
    }

    #[Test]
    public function island_can_be_rendered_from_php()
    {
        $testable = $this->mountIslandComponent();

        $testable->set('name', 'Statamic')->call('refreshStats');

        $fragments = $testable->effects['islandFragments'] ?? [];

        $this->assertCount(1, $fragments);
        $this->assertStringContainsString('Hello Statamic!', $fragments[0]);
        $this->assertStringContainsString('type=island|name=stats|token=antlers-', $fragments[0]);
    }

    #[Test]
    public function deferred_island_renders_placeholder_on_mount()
    {
        $testable = $this->mountIslandComponent('antlers-island-deferred');

        $testable->assertSee('Loading stats...');
        $testable->assertDontSee('Hello World!');
        $testable->assertSeeHtml('wire:init="__lazyLoadIsland"');
    }

    #[Test]
    public function lazy_island_renders_placeholder_on_mount()
    {
        $testable = $this->mountIslandComponent('antlers-island-lazy');

        $testable->assertSee('Loading stats...');
        $testable->assertDontSee('Hello World!');
        $testable->assertSeeHtml('wire:intersect.once="__lazyLoadIsland"');
    }

    #[Test]
    public function island_cache_files_are_regenerated_when_missing()
    {
        $testable = $this->mountIslandComponent();

        File::deleteDirectory(app('livewire.compiler')->cacheManager->cacheDirectory.'/islands');

        $testable->call('refreshStats');

        $fragments = $testable->effects['islandFragments'] ?? [];

        $this->assertCount(1, $fragments);
        $this->assertStringContainsString('Hello World!', $fragments[0]);
    }

    #[Test]
    public function island_renderer_renders_the_placeholder_branch()
    {
        $renderer = new IslandRenderer;

        $this->assertSame('<p>Loading...</p>', trim($renderer->render(['__placeholder' => ''], '<p>Hello {{ name }}!</p>', '<p>Loading...</p>')));
        $this->assertSame('', $renderer->render(['__placeholder' => ''], '<p>Hello {{ name }}!</p>'));
        $this->assertSame('<p>Hello World!</p>', trim($renderer->render(['name' => 'World'], '<p>Hello {{ name }}!</p>', '<p>Loading...</p>')));
    }

    #[Test]
    public function always_island_is_rendered_on_subsequent_renders()
    {
        $testable = $this->mountIslandComponent('antlers-island-always');

        $testable->call('$refresh');

        $testable->assertSee('Hello World!');
        $testable->assertDontSeeHtml('mode=skip');
    }

    #[Test]
    public function skipped_island_renders_placeholder_on_mount()
    {
        $testable = $this->mountIslandComponent('antlers-island-skip');

        $testable->assertSee('Skipped for now...');
        $testable->assertDontSee('Hello World!');
        $testable->assertDontSeeHtml('__lazyLoadIsland');
    }

    #[Test]
    public function nested_island_keeps_its_placeholder()
    {
        $testable = $this->mountIslandComponent('antlers-island-nested');

        $testable->assertSee('Outer island content');
        $testable->assertSee('Loading inner...');
        $testable->assertDontSee('Inner island content');
        $testable->assertSeeHtml('wire:init="__lazyLoadIsland"');
        $testable->assertSeeHtml('name=inner');
    }

    #[Test]
    public function directive_with_data_is_available_in_the_island()
    {
        $testable = $this->mountIslandComponent('antlers-island-with');

        $testable->assertSee('Hi World!');

        $testable->call('refreshStats');

        $fragments = $testable->effects['islandFragments'] ?? [];

        $this->assertCount(1, $fragments);
        $this->assertStringContainsString('Hi World!', $fragments[0]);
    }

    #[Test]
    public function directive_with_data_can_be_an_inline_array()
    {
        $testable = $this->mountIslandComponent('antlers-island-with-inline');

        $testable->assertSee('Inline World!');
    }

    #[Test]
    public function runtime_with_data_overrides_directive_with_data()
    {
        $testable = $this->mountIslandComponent('antlers-island-with');

        $testable->call('refreshStatsWithRuntimeData');

        $fragments = $testable->effects['islandFragments'] ?? [];

        $this->assertCount(1, $fragments);
        $this->assertStringContainsString('Runtime World!', $fragments[0]);
    }

    #[Test]
    public function island_token_stays_stable_when_dynamic_with_data_changes()
    {
        $testable = $this->mountIslandComponent('antlers-island-with-dynamic');

        $testable->assertSee('Hi World!');

        preg_match('/token=(antlers-[a-f0-9\-]+)/', $testable->html(), $matches);
        $token = $matches[1];

        $testable->set('greeting', 'Yo');

        $testable->assertSeeHtml('token='.$token);

        $testable->call('refreshStats');

        $fragments = $testable->effects['islandFragments'] ?? [];

        $this->assertCount(1, $fragments);
        $this->assertStringContainsString('token='.$token, $fragments[0]);
        $this->assertStringContainsString('Hi World!', $fragments[0]);
    }

    #[Test]
    public function island_cache_files_are_regenerated_when_missing_after_dynamic_with_data_changed()
    {
        $testable = $this->mountIslandComponent('antlers-island-with-dynamic');

        $testable->set('greeting', 'Yo');

        File::deleteDirectory(app('livewire.compiler')->cacheManager->cacheDirectory.'/islands');
        File::cleanDirectory(config('view.compiled'));

        $testable->call('refreshStats');

        $fragments = $testable->effects['islandFragments'] ?? [];

        $this->assertCount(1, $fragments);
        $this->assertStringContainsString('Yo World!', $fragments[0]);
    }

    #[Test]
    public function same_name_islands_render_independently()
    {
        $testable = $this->mountIslandComponent('antlers-island-same-name');

        $testable->assertSee('First stats island: World');
        $testable->assertSee('Second stats island: World');

        $testable->call('refreshStats');

        $this->assertCount(2, $testable->effects['islandFragments'] ?? []);
    }

    #[Test]
    public function same_identity_islands_keep_their_own_tokens()
    {
        $testable = $this->mountIslandComponent('antlers-island-same-identity');

        $testable->assertSee('Hi World from a twin island!');
        $testable->assertSee('Yo World from a twin island!');

        preg_match_all('/token=(antlers-[a-f0-9\-]+)/', $testable->html(), $matches);
        $tokens = array_unique($matches[1]);

        $this->assertCount(2, $tokens);

        $testable->call('$refresh');

        foreach ($tokens as $token) {
            $testable->assertSeeHtml('token='.$token);
        }
    }

    #[Test]
    public function placeholder_inside_an_antlers_comment_is_not_extracted()
    {
        $testable = $this->mountIslandComponent('antlers-island-commented-placeholder');

        $testable->assertDontSee('Hidden placeholder');
        $testable->assertDontSee('Hello World!');
        $testable->assertSeeHtml('wire:init="__lazyLoadIsland"');
    }

    #[Test]
    public function island_tag_requires_a_name()
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('The {{ livewire:island }} tag requires a name parameter.');

        $this->mountIslandComponent('antlers-island-nameless');
    }

    #[Test]
    public function island_name_may_not_contain_special_characters()
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('The {{ livewire:island }} name may only contain letters, numbers, underscores, dashes and dots.');

        $this->mountIslandComponent('antlers-island-invalid-name');
    }

    /**
     * The view name is passed as a mount parameter so it survives Livewire
     * re-instantiating the component on every request.
     */
    protected function mountIslandComponent(string $view = 'antlers-island'): Testable
    {
        $component = new class extends Component
        {
            public string $viewName = 'antlers-island';

            public string $name = 'World';

            public string $greeting = 'Hi';

            public array $withData = ['greeting' => 'Hi'];

            public function refreshStats(): void
            {
                $this->renderIsland('stats');
            }

            public function refreshStatsWithRuntimeData(): void
            {
                $this->renderIsland('stats', with: ['greeting' => 'Runtime']);
            }

            public function render()
            {
                return view($this->viewName);
            }
        };

        return Livewire::test($component, ['viewName' => $view]);
    }
}
