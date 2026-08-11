<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Counter extends Component
{
    public int $count = 0;

    public string $label = 'Counter';

    public function increment(): void
    {
        $this->count++;
    }

    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('livewire.counter');
    }
}
