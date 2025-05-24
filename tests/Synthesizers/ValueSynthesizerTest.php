<?php

namespace MarcoRieser\Livewire\Tests\Synthesizers;

use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Livewire;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Fields\Value;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

use function Livewire\invade;

class ValueSynthesizerTest extends SynthesizerTestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function value_is_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertSet('value', Entry::find(1)->augmentedValue('title'));
    }

    #[Test]
    #[DefineEnvironment('disableSynthesizers')]
    public function value_is_not_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        $this->expectException(ViewException::class);
        $this->expectExceptionMessageMatches('/Property type not supported in Livewire/');

        Livewire::test($component);
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function value_gets_dehydrated_and_rehydrated_correctly()
    {
        $component = $this->getLivewireComponent();

        $testable = Livewire::test($component)->refresh();

        $expected = Entry::find(1)->augmentedValue('title');

        $this->assertInstanceOf(Value::class, $testable->value);

        $expected = invade($expected);
        $value = invade($testable->value);

        $this->assertEquals($expected->resolver, $value->resolver);
        $this->assertEquals($expected->raw, $value->raw);
        $this->assertEquals($expected->handle, $value->handle);
        $this->assertEquals(get_class($expected->fieldtype), get_class($value->fieldtype));
        $this->assertEquals(get_class($expected->augmentable), get_class($value->augmentable));
        $this->assertEquals($expected->shallow, $value->shallow);
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
            public Value $value;

            public function mount()
            {
                $this->value = Entry::find(1)->augmentedValue('title');
            }

            public function render()
            {
                return '<div></div>';
            }
        };
    }
}
