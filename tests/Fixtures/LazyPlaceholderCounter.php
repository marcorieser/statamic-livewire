<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests\Fixtures;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class LazyPlaceholderCounter extends Component
{
    public function placeholder(): string
    {
        return '<div>waiting…</div>';
    }

    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('counter-view');
    }
}
