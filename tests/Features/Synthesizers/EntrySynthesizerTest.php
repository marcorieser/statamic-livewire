<?php

namespace MarcoRieser\Livewire\Tests\Synthesizers;

use Illuminate\Support\Carbon;
use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Livewire;
use MarcoRieser\Livewire\Tests\Concerns\CanManipulateAddonConfig;
use MarcoRieser\Livewire\Tests\TestCase;
use Orchestra\Testbench\Attributes\DefineEnvironment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Collection;
use Statamic\Facades\Entry;
use Statamic\Fields\Value;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class EntrySynthesizerTest extends TestCase
{
    use CanManipulateAddonConfig;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function entry_is_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertSet('entry', Entry::find('1'));
    }

    #[Test]
    #[DefineEnvironment('disableSynthesizers')]
    public function entry_is_not_supported_as_property_type()
    {
        $component = $this->getLivewireComponent();

        $this->expectException(ViewException::class);
        $this->expectExceptionMessageMatches('/Property type not supported in Livewire/');

        Livewire::test($component);
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    #[DefineEnvironment('enableSynthesizerAugmentation')]
    public function entry_gets_augmented_in_view()
    {
        $component = $this->getLivewireComponent();

        $testable = Livewire::test($component);

        $testable->assertViewHas('entry', Entry::find('1')->toAugmentedArray());
        $this->assertInstanceOf(Value::class, $testable->viewData('entry')['id']);
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    #[DefineEnvironment('disableSynthesizerAugmentation')]
    public function entry_gets_not_augmented_in_view()
    {
        $component = $this->getLivewireComponent();

        Livewire::test($component)
            ->assertViewHas('entry', Entry::find('1'));
    }

    #[Test]
    #[DefineEnvironment('enableSynthesizers')]
    public function entry_gets_dehydrated_and_rehydrated_correctly()
    {
        $component = $this->getLivewireComponent();

        $testable = Livewire::test($component)->refresh();

        $expected = Entry::find('1');

        $this->assertInstanceOf(\Statamic\Contracts\Entries\Entry::class, $testable->entry);
        $this->assertEquals($expected, $testable->entry);
        $this->assertSame($expected->id(), $testable->entry->id());
        $this->assertSame($expected->collection(), $testable->entry->collection());
        $this->assertSame($expected->origin(), $testable->entry->origin());
        $this->assertSame($expected->blueprint(), $testable->entry->blueprint());
        $this->assertSame($expected->locale(), $testable->entry->locale());
        $this->assertSame($expected->initialPath(), $testable->entry->initialPath());
        $this->assertSame($expected->published(), $testable->entry->published());
        $this->assertSame($expected->data()->all(), $testable->entry->data()->all());
        $this->assertSame($expected->slug(), $testable->entry->slug());
        $this->assertSame($expected->date()->timestamp, $testable->entry->date()->timestamp);
    }

    protected function setUp(): void
    {
        parent::setUp();

        Collection::make('entries')->dated(true)->save();

        Entry::make()
            ->id('origin-1')
            ->collection('entries')
            ->data(['title' => 'Origin Entry 1'])
            ->save();

        Entry::make()
            ->id('1')
            ->collection('entries')
            ->origin('origin-1')
            ->blueprint('entry')
            ->locale('default')
            ->published(false)
            ->data(['title' => 'Entry 1'])
            ->slug('entry-1')
            ->date(Carbon::now())
            ->save();
    }

    protected function getLivewireComponent(): Component
    {
        return new class extends Component
        {
            public \Statamic\Entries\Entry $entry;

            public function mount()
            {
                $this->entry = Entry::find('1');
            }

            public function render()
            {
                return '<div></div>';
            }
        };
    }
}
