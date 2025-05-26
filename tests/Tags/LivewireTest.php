<?php

namespace MarcoRieser\Livewire\Tests\Tags;

use Livewire\Component;
use Livewire\Livewire;
use MarcoRieser\Livewire\Testing\Concerns\CanManipulateAddonConfig;
use MarcoRieser\Livewire\Tests\TestCase;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Facades\Parse;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class LivewireTest extends TestCase
{
    use CanManipulateAddonConfig;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    #[DefineEnvironment('disableSynthesizers')]
    public function parameters_are_converted_to_an_array_when_passed_to_a_component_if_synthesizers_are_disabled()
    {
        $component = new class extends Component
        {
            public \Statamic\Contracts\Entries\Entry $entry;

            public function render()
            {
                return '<div></div>';
            }
        };

        $component->setName('test');

        $entry = Entry::find('1');

        Livewire::expects('mount')->with('test', ['entry' => $entry->toArray()], null);

        Parse::template('{{ livewire:test :entry="entry" /}}', ['entry' => $entry]);
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function parameters_keep_their_type_when_passed_to_a_component_if_synthesizers_are_enabled()
    {
        $component = new class extends Component
        {
            public \Statamic\Contracts\Entries\Entry $entry;

            public function render()
            {
                return '<div></div>';
            }
        };

        $component->setName('test');

        $entry = Entry::find('1');

        Livewire::expects('mount')->with('test', ['entry' => $entry], null);

        Parse::template('{{ livewire:test :entry="entry" /}}', ['entry' => $entry]);
    }

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
}
