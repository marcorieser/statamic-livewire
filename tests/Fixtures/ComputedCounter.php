<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests\Fixtures;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ComputedCounter extends Component
{
    public int $count = 2;

    #[Computed]
    public function double(): int
    {
        return $this->count * 2;
    }

    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('computed-counter');
    }
}
