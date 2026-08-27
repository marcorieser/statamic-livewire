<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;

class CounterBlade extends Counter
{
    public function render(): View
    {
        return view()->make('livewire.counter-blade');
    }
}
