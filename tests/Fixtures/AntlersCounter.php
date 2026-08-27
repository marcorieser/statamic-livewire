<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests\Fixtures;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class AntlersCounter extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('antlers-counter');
    }
}
