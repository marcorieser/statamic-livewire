<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;

class IslandDashboardBlade extends IslandDashboard
{
    public function render(): View
    {
        return view()->make('livewire.island-dashboard-blade');
    }
}
