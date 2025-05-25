<?php

namespace MarcoRieser\Livewire\Tests\Synthesizers;

use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Livewire;
use MarcoRieser\Livewire\Testing\Concerns\CanManipulateAddonConfig;
use MarcoRieser\Livewire\Tests\TestCase;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EntryCollectionSynthesizerTest extends TestCase
{
    use CanManipulateAddonConfig;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function entry_collection_is_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertSet('entries', Entry::all());
    }

    #[Test]
    #[DefineEnvironment('disableSynthesizers')]
    public function entry_collection_is_not_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        $this->expectException(ViewException::class);
        $this->expectExceptionMessageMatches('/Property type not supported in Livewire/');

        Livewire::test($component);
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    #[DefineEnvironment('enableSynthesizerAugmentation')]
    public function entry_collection_gets_augmented_in_view()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertViewHas('entries', Entry::all()->toAugmentedArray());
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    #[DefineEnvironment('disableSynthesizerAugmentation')]
    public function entry_collection_gets_not_augmented_in_view()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertViewHas('entries', Entry::all());
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function entry_collection_gets_dehydrated_and_rehydrated_correctly()
    {
        $component = $this->getLivewireComponent();

        $testable = Livewire::test($component)->refresh();

        $this->assertInstanceOf(EntryCollection::class, $testable->entries);
        $this->assertInstanceOf(\Statamic\Contracts\Entries\Entry::class, $testable->entries->first());
        $this->assertEquals(Entry::all(), $testable->entries);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('entries')->save();

        Entry::make()
            ->id('1')
            ->collection('entries')
            ->blueprint('entry')
            ->locale('default')
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
