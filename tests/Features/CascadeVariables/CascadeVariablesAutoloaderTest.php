<?php

namespace MarcoRieser\Livewire\Tests\Hooks;

use Illuminate\Support\Collection;
use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Livewire;
use MarcoRieser\Livewire\Attributes\Cascade;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Cascade as CascadeFacade;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class CascadeVariablesAutoloaderTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function cascade_variables_are_autoloaded_in_antlers()
    {
        $component = $this->getAntlersLivewireComponent();

        $testable = Livewire::test($component);

        $testable->assertViewHas('homepage', '/');
        $testable->assertViewHas('environment', 'testing');
    }

    #[Test]
    public function cascade_variables_are_autoloaded_in_blade()
    {
        $component = $this->getBladeLivewireComponent();

        $testable = Livewire::test($component);

        $testable->assertViewHas('homepage');
        $testable->assertViewHas('environment');
    }

    #[Test]
    public function cascade_variables_are_autoloaded_selectively()
    {
        $component = $this->getSelectedLivewireComponent();

        $testable = Livewire::test($component);

        $testable->assertViewHas('homepage', '/');
        $testable->assertViewHas('my_global', true);
        $testable->assertViewMissing('environment');
    }

    #[Test]
    public function cascade_variables_throw_exception_when_invalid()
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('Cascade data [my_invalid] not found');

        $component = $this->getInvalidLivewireComponent();

        Livewire::test($component);
    }

    #[Test]
    public function cascade_variables_are_not_autoloaded_when_attribute_excluded()
    {
        $component = $this->getExcludedLivewireComponent();

        $testable = Livewire::test($component);

        $testable->assertViewMissing('homepage');
        $testable->assertViewMissing('environment');
    }

    #[Test]
    public function cascade_variables_include_context_data_when_context_attribute_is_present()
    {
        $this->seedCascadeWithSections([
            [
                'id' => 'section-abc',
                'type' => 'hero',
                'heading' => 'Hero Heading',
            ],
            [
                'id' => 'section-def',
                'type' => 'text',
                'body' => 'Some text content',
            ],
        ]);

        $component = $this->getContextLivewireComponent();

        $testable = Livewire::test($component, ['_context' => 'section-abc']);

        $testable->assertViewHas('type', 'hero');
        $testable->assertViewHas('heading', 'Hero Heading');
    }

    #[Test]
    public function cascade_variables_with_context_merges_over_base_cascade()
    {
        $this->seedCascadeWithSections([
            [
                'id' => 'section-abc',
                'environment' => 'overridden',
            ],
        ]);

        $component = $this->getContextLivewireComponent();

        $testable = Livewire::test($component, ['_context' => 'section-abc']);

        $testable->assertViewHas('environment', 'overridden');
    }

    #[Test]
    public function cascade_variables_without_context_attribute_do_not_include_nested_data()
    {
        $this->seedCascadeWithSections([
            [
                'id' => 'section-abc',
                'heading' => 'Hero Heading',
            ],
        ]);

        $component = $this->getAntlersLivewireComponent();

        $testable = Livewire::test($component);

        $testable->assertViewMissing('heading');
    }

    #[Test]
    public function cascade_variables_with_nonexistent_context_returns_base_cascade()
    {
        $this->seedCascadeWithSections([]);

        $component = $this->getContextLivewireComponent();

        $testable = Livewire::test($component, ['_context' => 'nonexistent-id']);

        $testable->assertViewHas('homepage');
        $testable->assertViewHas('environment', 'testing');
    }

    #[Test]
    public function cascade_variables_with_custom_context_attribute_name()
    {
        $this->seedCascadeWithSections([
            [
                'id' => 'section-abc',
                'heading' => 'Custom Context',
            ],
        ]);

        $component = $this->getCustomContextAttributeLivewireComponent();

        $testable = Livewire::test($component, ['_my_context' => 'section-abc']);

        $testable->assertViewHas('heading', 'Custom Context');
    }

    #[Test]
    public function cascade_variables_with_deeply_nested_context()
    {
        CascadeFacade::hydrate();

        $block = new Collection([
            'id' => 'block-deep',
            'content' => 'Deeply nested',
        ]);

        $section = new Collection([
            'id' => 'section-1',
            'blocks' => [$block],
        ]);

        $page = new Collection([
            'id' => 'page-1',
            'sections' => [$section],
        ]);

        CascadeFacade::set('page', $page);
        CascadeFacade::set('sections', [$section]);

        $component = $this->getContextLivewireComponent();

        $testable = Livewire::test($component, ['_context' => 'block-deep']);

        $testable->assertViewHas('content', 'Deeply nested');
    }

    protected function getContextLivewireComponent(): Component
    {
        return new
        #[Cascade]
        class extends Component
        {
            public function render()
            {
                return view('antlers');
            }
        };
    }

    protected function getCustomContextAttributeLivewireComponent(): Component
    {
        return new
        #[Cascade(contextAttribute: '_my_context')]
        class extends Component
        {
            public function render()
            {
                return view('antlers');
            }
        };
    }

    protected function seedCascadeWithSections(array $sections): void
    {
        CascadeFacade::hydrate();

        $sections = array_map(fn ($section) => new Collection($section), $sections);

        $page = new Collection([
            'id' => 'page-1',
            'sections' => $sections,
        ]);

        CascadeFacade::set('page', $page);
        CascadeFacade::set('sections', $sections);
    }

    protected function getAntlersLivewireComponent(): Component
    {
        return new
        #[Cascade]
        class extends Component
        {
            public function render()
            {
                return view('antlers');
            }
        };
    }

    protected function getBladeLivewireComponent(): Component
    {
        return new
        #[Cascade]
        class extends Component
        {
            public function render()
            {
                return view('blade');
            }
        };
    }

    protected function getSelectedLivewireComponent(): Component
    {
        return new
        #[Cascade(['homepage', 'my_global' => true])]
        class extends Component
        {
            public function render()
            {
                return view('antlers');
            }
        };
    }

    protected function getInvalidLivewireComponent(): Component
    {
        return new
        #[Cascade(['my_invalid'])]
        class extends Component
        {
            public function render()
            {
                return view('antlers');
            }
        };
    }

    protected function getExcludedLivewireComponent(): Component
    {
        return new class extends Component
        {
            public function render()
            {
                return view('antlers');
            }
        };
    }
}
