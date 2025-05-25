<?php

namespace MarcoRieser\Livewire\Tests\Hooks;

use Livewire\Component;
use Livewire\Livewire;
use MarcoRieser\Livewire\Testing\Concerns\CanManipulateAddonConfig;
use MarcoRieser\Livewire\Tests\TestCase;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class LivewireTest extends TestCase
{
    use CanManipulateAddonConfig;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function parameters_keep_their_type_when_passed_to_a_component()
    {
        $component = new class extends Component
        {
            public \Statamic\Contracts\Entries\Entry $entry;

            public function render()
            {
                return '<div></div>';
            }
        };

        $entry = Entry::find('1');

        $testable = Livewire::test($component, ['entry' => $entry]);

        $testable->assertSetStrict('entry', $entry);
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
