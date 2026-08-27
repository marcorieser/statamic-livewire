<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests\Fixtures;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use MarcoRieser\Livewire\Attributes\Cascade;

#[Cascade(['definitely_not_a_cascade_key'])]
class MissingCascadeKeyViewer extends Component
{
    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('cascade-viewer');
    }
}
