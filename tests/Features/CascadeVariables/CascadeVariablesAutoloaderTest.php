<?php

namespace MarcoRieser\Livewire\Tests\Hooks;

use Illuminate\View\ViewException;
use Livewire\Component;
use Livewire\Livewire;
use MarcoRieser\Livewire\Attributes\Cascade;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class CascadeVariablesAutoloaderTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function cascade_variables_are_autoloaded_in_antlers()
    {
        $component = $this->getAntlersLivewireComponent();

        $testable = Livewire::test($component);

        $testable->assertViewHas('homepage', '/');
        $testable->assertViewHas('environment', 'testing');
    }

    #[Test]
    public function cascade_variables_are_not_autoloaded_in_blade()
    {
        $component = $this->getBladeLivewireComponent();

        $testable = Livewire::test($component);

        $testable = $testable->assertViewMissing('homepage');
        $testable = $testable->assertViewMissing('environment');
    }

    #[Test]
    public function cascade_variables_are_autoloaded_selectively()
    {
        $component = $this->getSelectedLivewireComponent();

        $testable = Livewire::test($component);

        $testable = $testable->assertViewHas('homepage', '/');
        $testable = $testable->assertViewHas('my_global', true);
        $testable = $testable->assertViewMissing('environment');
    }

    #[Test]
    public function cascade_variables_throw_exception_when_invalid()
    {
        $this->expectException(ViewException::class);
        $this->expectExceptionMessage('Cascade data [my_invalid] not found');

        $component = $this->getInvalidLivewireComponent();

        $testable = Livewire::test($component);
    }

    #[Test]
    public function cascade_variables_are_not_autoloaded_when_attribute_excluded()
    {
        $component = $this->getExcludedLivewireComponent();

        $testable = Livewire::test($component);

        $testable = $testable->assertViewMissing('homepage');
        $testable = $testable->assertViewMissing('environment');
    }

    protected function getAntlersLivewireComponent(): Component
    {
        return new
        #[Cascade]
        class extends Component
        {
            public function render()
            {
                return view('antlers');
            }
        };
    }

    protected function getBladeLivewireComponent(): Component
    {
        return new
        #[Cascade]
        class extends Component
        {
            public function render()
            {
                return view('blade');
            }
        };
    }

    protected function getSelectedLivewireComponent(): Component
    {
        return new
        #[Cascade(['homepage', 'my_global' => true])]
        class extends Component
        {
            public function render()
            {
                return view('antlers');
            }
        };
    }

    protected function getInvalidLivewireComponent(): Component
    {
        return new
        #[Cascade(['my_invalid'])]
        class extends Component
        {
            public function render()
            {
                return view('antlers');
            }
        };
    }

    protected function getExcludedLivewireComponent(): Component
    {
        return new class extends Component
        {
            public function render()
            {
                return view('antlers');
            }
        };
    }
}
