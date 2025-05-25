<?php

namespace MarcoRieser\Livewire\Tests\Synthesizers;

use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Livewire;
use MarcoRieser\Livewire\Testing\Concerns\CanManipulateAddonConfig;
use MarcoRieser\Livewire\Tests\TestCase;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Fields\Field;
use Statamic\Fields\Value;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class FieldSynthesizerTest extends TestCase
{
    use CanManipulateAddonConfig;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function field_is_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertSet('field', Entry::find('1')->blueprint()->field('title'));
    }

    #[Test]
    #[DefineEnvironment('disableSynthesizers')]
    public function field_is_not_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        $this->expectException(ViewException::class);
        $this->expectExceptionMessageMatches('/Property type not supported in Livewire/');

        Livewire::test($component);
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    #[DefineEnvironment('enableSynthesizerAugmentation')]
    public function field_gets_augmented_in_view()
    {
        $component = $this->getLivewireComponent();

        $testable = Livewire::test($component);
        $testable->assertViewHas('field', Entry::find('1')->blueprint()->field('title')->augment());
        $this->assertInstanceOf(Value::class, $testable->viewData('field')->value());
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    #[DefineEnvironment('disableSynthesizerAugmentation')]
    public function field_gets_not_augmented_in_view()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertViewHas('field', Entry::find('1')->blueprint()->field('title'));
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function field_gets_dehydrated_and_rehydrated_correctly()
    {
        $component = $this->getLivewireComponent();

        $testable = Livewire::test($component)->refresh();

        $expected = Entry::find('1')->blueprint()->field('title');

        $this->assertInstanceOf(Field::class, $testable->field);
        $this->assertEquals($expected, $testable->field);
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
            public Field $field;

            public function mount()
            {
                $this->field = Entry::find(1)->blueprint()->field('title');
            }

            public function render()
            {
                return '<div></div>';
            }
        };
    }
}
