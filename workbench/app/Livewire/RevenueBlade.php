<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;

class RevenueBlade extends Revenue
{
    public function placeholder(): string
    {
        return '<div class="card">Loading revenue (blade)…</div>';
    }

    public function render(): View
    {
        return view()->make('livewire.revenue-blade');
    }
}
