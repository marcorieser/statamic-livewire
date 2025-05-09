<?php

namespace MarcoRieser\Livewire\Tests\Synthesizers;

use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Livewire;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EntryCollectionSynthesizerTest extends SynthesizerTestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function entries_collection_is_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertSet('entries', Entry::all());
    }

    #[Test]
    #[DefineEnvironment('disableSynthesizers')]
    public function entries_collection_is_not_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        $this->expectException(ViewException::class);
        $this->expectExceptionMessageMatches('/Property type not supported in Livewire/');

        Livewire::test($component);
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    #[DefineEnvironment('enableSynthesizerTransform')]
    public function entries_collection_gets_augmented_in_view()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertViewHas('entries', Entry::all()->toAugmentedArray());
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    #[DefineEnvironment('disableSynthesizerTransform')]
    public function entries_collection_gets_not_augmented_in_view()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertViewHas('entries', Entry::all());
    }

    // TODO[mr]: test de-/rehydration (09.05.2025 mr)

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('entries')->save();

        Entry::make()
            ->collection('entries')
            ->id('1')
            ->data(['title' => 'Entry 1'])
            ->save();
    }

    protected function getLivewireComponent(): Component
    {
        return new class extends Component
        {
            public EntryCollection $entries;

            public function mount()
            {
                $this->entries = Entry::all();
            }

            public function render()
            {
                return '<div></div>';
            }
        };
    }
}
