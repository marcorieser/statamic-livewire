<?php

namespace MarcoRieser\Livewire\Tests\Synthesizers;

use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Livewire;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Fields\Fieldtype;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class FieldtypeSynthesizerTest extends SynthesizerTestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function fieldtype_is_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertSet('fieldtype', Entry::find('1')->blueprint()->field('slug')->fieldtype());
    }

    #[Test]
    #[DefineEnvironment('disableSynthesizers')]
    public function fieldtype_is_not_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        $this->expectException(ViewException::class);
        $this->expectExceptionMessageMatches('/Property type not supported in Livewire/');

        Livewire::test($component);
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function field_gets_dehydrated_and_rehydrated_correctly()
    {
        $component = $this->getLivewireComponent();

        $testable = Livewire::test($component)->refresh();

        $expected = Entry::find('1')->blueprint()->field('slug')->fieldtype();

        $this->assertInstanceOf(Fieldtype::class, $testable->fieldtype);
        $this->assertEquals($expected, $testable->fieldtype);
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

    protected function getLivewireComponent(): Component
    {
        return new class extends Component
        {
            public Fieldtype $fieldtype;

            public function mount()
            {
                $this->fieldtype = Entry::find('1')->blueprint()->field('slug')->fieldtype();
            }

            public function render()
            {
                return '<div></div>';
            }
        };
    }
}
