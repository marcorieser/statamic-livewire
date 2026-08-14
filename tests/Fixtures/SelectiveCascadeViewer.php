<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests\Fixtures;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use MarcoRieser\Livewire\Attributes\Cascade;

#[Cascade(['current_url', 'not_in_cascade' => 'fallback-value'])]
class SelectiveCascadeViewer extends Component
{
    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('selective-cascade-viewer');
    }
}
