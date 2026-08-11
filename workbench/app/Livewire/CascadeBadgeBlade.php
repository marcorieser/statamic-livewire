<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;

/**
 * No #[Cascade] here on purpose — the attribute only exposes cascade data to
 * Antlers views (see CascadeVariablesAutoloader), so it wouldn't do anything
 * for this Blade view anyway.
 */
class CascadeBadgeBlade extends CascadeBadge
{
    public function render(): View
    {
        return view()->make('livewire.cascade-badge-blade');
    }
}
