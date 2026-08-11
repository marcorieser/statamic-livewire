<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;

/**
 * No #[Cascade] here — the view uses Statamic's native @cascade Blade
 * directive instead (see livewire.cascade-badge-blade).
 */
class CascadeBadgeBlade extends CascadeBadge
{
    public function render(): View
    {
        return view()->make('livewire.cascade-badge-blade');
    }
}
