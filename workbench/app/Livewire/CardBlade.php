<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;

class CardBlade extends Card
{
    public function render(): View
    {
        return view()->make('livewire.card-blade');
    }
}
