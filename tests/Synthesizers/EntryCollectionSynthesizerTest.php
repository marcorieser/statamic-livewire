<?php

namespace MarcoRieser\Livewire\Tests\Synthesizers;

use Livewire\Component;
use Livewire\Livewire;
use MarcoRieser\Livewire\Tests\TestCase;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Entries\EntryCollection;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EntryCollectionSynthesizerTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function data_is_augmented_to_array()
    {
        Collection::make('entries')->save();

        Entry::make()
            ->collection('entries')
            ->id('1')
            ->data(['title' => 'Entry 1'])
            ->save();

        $component = new class extends Component
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
        Livewire::test($component)
            ->assertSet('entries', Entry::all());
        //         assert that the data is augmented to an augmented array (and eg. first entry's title is a value object)

    }
}
