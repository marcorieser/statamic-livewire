<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Revenue extends Component
{
    public int $total = 4213;

    public function placeholder(): string
    {
        return '<div class="card">Loading revenue…</div>';
    }

    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('livewire.revenue');
    }
}
