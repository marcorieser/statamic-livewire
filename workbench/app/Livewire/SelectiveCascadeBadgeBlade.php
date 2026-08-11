<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;

/**
 * No #[Cascade] here on purpose — see CascadeBadgeBlade.
 */
class SelectiveCascadeBadgeBlade extends SelectiveCascadeBadge
{
    public function render(): View
    {
        return view()->make('livewire.selective-cascade-badge-blade');
    }
}
