<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use MarcoRieser\Livewire\Attributes\Cascade;

#[Cascade(['title', 'author' => 'Anonymous'])]
class SelectiveCascadeBadge extends Component
{
    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('livewire.selective-cascade-badge');
    }
}
