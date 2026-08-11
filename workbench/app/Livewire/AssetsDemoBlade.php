<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;

class AssetsDemoBlade extends AssetsDemo
{
    public function render(): View
    {
        return view()->make('livewire.assets-demo-blade');
    }
}
