<?php

namespace MarcoRieser\Livewire\Tests\Features\Islands;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\View\ViewException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use MarcoRieser\Livewire\Exceptions\IslandException;
use MarcoRieser\Livewire\Islands\IslandRenderer;
use MarcoRieser\Livewire\Islands\WithSnapshot;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AntlersIslandsTest extends TestCase
{
    /**
     * The island cache in the testbench skeleton survives across PHPUnit
     * processes, so stale files from previous runs would otherwise leak in.
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

    /**
     * The regeneration render must go through Livewire's render hooks so
     * autoloaded data (computed properties, Cascade) is available — otherwise
     * islands inside conditions depending on it are silently dropped.
     */
    #[Test]
    public function islands_inside_computed_property_conditions_are_regenerated_when_missing()
    {
        $testable = $this->mountIslandComponent('antlers-island-computed-condition');

        $testable->assertSee('Hello World!');

        File::deleteDirectory(app('livewire.compiler')->cacheManager->cacheDirectory.'/islands');
        File::cleanDirectory(config('view.compiled'));

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
    public function with_data_round_trips_rich_values_through_the_snapshot_pipeline()
    {
        $snapshot = app(WithSnapshot::class)->snapshot(['released' => Carbon::parse('2026-06-10 12:00:00')]);

        $with = app(WithSnapshot::class)->resurrect($snapshot);

        $this->assertInstanceOf(Carbon::class, $with['released']);
        $this->assertSame('2026-06-10 12:00:00', $with['released']->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function non_dehydratable_with_data_throws_an_island_exception()
    {
        $this->expectException(IslandException::class);
        $this->expectExceptionMessage('The with data of the {{ livewire:island }} tag could not be dehydrated');

        app(WithSnapshot::class)->snapshot(['callback' => fn () => null]);
    }

    /**
     * Snapshots persisted in cache files would put captured values on disk
     * and break fresh mounts once their release token or checksum expires.
     */
    #[Test]
    public function with_snapshots_are_stored_in_the_islands_memo_instead_of_the_cache_files()
    {
        $testable = $this->mountIslandComponent('antlers-island-with');

        $islands = $testable->snapshot['memo']['islands'] ?? [];

        $this->assertNotEmpty($islands);
        $this->assertArrayHasKey('with', $islands[0]);
        $this->assertArrayHasKey('memo', $islands[0]['with']);

        $files = File::allFiles(app('livewire.compiler')->cacheManager->cacheDirectory.'/islands');

        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $this->assertStringNotContainsString('json_decode', $file->getContents());
        }
    }

    #[Test]
    public function fresh_mounts_render_islands_after_a_release_token_change()
    {
        $this->mountIslandComponent('antlers-island-with');

        config()->set('livewire.release_token', 'release-2');

        $testable = $this->mountIslandComponent('antlers-island-with');

        $testable->assertSee('Hi World!');
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

    /**
     * Nested island tags only run while their containing island renders, so
     * the regeneration pass has to descend into the outer island's view.
     */
    #[Test]
    public function nested_island_cache_files_are_regenerated_when_missing()
    {
        $testable = $this->mountIslandComponent('antlers-island-nested');

        File::deleteDirectory(app('livewire.compiler')->cacheManager->cacheDirectory.'/islands');
        File::cleanDirectory(config('view.compiled'));

        $testable->call('refreshInner');

        $fragments = $testable->effects['islandFragments'] ?? [];

        $this->assertCount(1, $fragments);
        $this->assertStringContainsString('Inner island content', $fragments[0]);
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

    /**
     * A cache clear regenerates the template but keeps the mount-time "with" values.
     */
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
        $this->assertStringContainsString('Hi World!', $fragments[0]);
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

    /**
     * Changing every dynamic "with" value used to collapse both twins onto
     * the first memoized token, cross-wiring their fragments.
     */
    #[Test]
    public function twin_islands_keep_their_own_tokens_when_all_dynamic_with_data_changes()
    {
        $testable = $this->mountIslandComponent('antlers-island-same-identity-dynamic');

        preg_match_all('/token=(antlers-[a-f0-9\-]+)/', $testable->html(), $matches);
        $tokens = array_values(array_unique($matches[1]));

        $this->assertCount(2, $tokens);

        $testable->set('greetingA', 'Hey');
        $testable->set('greetingB', 'Yaw');

        $testable->call('$refresh');

        foreach ($tokens as $token) {
            $testable->assertSeeHtml('token='.$token);
        }
    }

    /**
     * Tokens are independent of the island template, so clients that mounted
     * before a template edit keep addressing the same island and receive the
     * updated content, mirroring core's path-based tokens.
     */
    #[Test]
    public function island_tokens_stay_stable_when_the_island_template_changes()
    {
        $testable = $this->mountIslandComponent();

        preg_match('/token=(antlers-[a-f0-9\-]+)/', $testable->html(), $matches);
        $token = $matches[1];

        $edited = $this->mountIslandComponent('antlers-island-edited');

        $edited->assertSee('Howdy World!');
        $edited->assertSeeHtml('token='.$token);

        $testable->call('refreshStats');

        $fragments = $testable->effects['islandFragments'] ?? [];

        $this->assertCount(1, $fragments);
        $this->assertStringContainsString('Howdy World!', $fragments[0]);
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
     * re-instantiating the component on every request. The component is
     * registered under a fixed name as tokens derive from it.
     */
    protected function mountIslandComponent(string $view = 'antlers-island'): Testable
    {
        $component = new class extends Component
        {
            public string $viewName = 'antlers-island';

            public string $name = 'World';

            public string $greeting = 'Hi';

            public string $greetingA = 'Hi';

            public string $greetingB = 'Yo';

            public array $withData = ['greeting' => 'Hi'];

            public function refreshStats(): void
            {
                $this->renderIsland('stats');
            }

            public function refreshStatsWithRuntimeData(): void
            {
                $this->renderIsland('stats', with: ['greeting' => 'Runtime']);
            }

            public function refreshInner(): void
            {
                $this->renderIsland('inner');
            }

            #[Computed]
            public function showStats(): bool
            {
                return true;
            }

            public function render()
            {
                return view($this->viewName);
            }
        };

        Livewire::component('antlers-island-component', $component::class);

        return Livewire::test('antlers-island-component', ['viewName' => $view]);
    }
}
