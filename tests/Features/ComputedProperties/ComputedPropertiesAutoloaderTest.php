<?php

namespace MarcoRieser\Livewire\Tests\Hooks;

use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\Livewire;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Fields\Value;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class ComputedPropertiesAutoloaderTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function computed_properties_are_autoloaded_in_antlers()
    {
        $component = $this->getAntlersLivewireComponent();

        $testable = Livewire::test($component);

        $testable->assertViewHas('entry');
        $this->assertInstanceOf(Value::class, $testable->viewData('entry'));
        $this->assertEquals(Entry::find('1'), $testable->entry);
    }

    #[Test]
    public function computed_properties_are_not_autoloaded_in_blade()
    {
        $component = $this->getBladeLivewireComponent();

        Livewire::test($component)->assertViewMissing('entry');
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

    protected function getAntlersLivewireComponent(): Component
    {
        return new class extends Component
        {
            public function render()
            {
                return view('antlers');
            }

            #[Computed]
            public function entry()
            {
                return Entry::find('1');
            }
        };
    }

    protected function getBladeLivewireComponent(): Component
    {
        return new class extends Component
        {
            public function render()
            {
                return view('blade');
            }

            #[Computed]
            public function entry()
            {
                return Entry::find('1');
            }
        };
    }
}
