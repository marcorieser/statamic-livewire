<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests\Fixtures;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class IslandCounter extends Component
{
    public int $count = 0;

    /** @var list<int> */
    public array $items = [1, 2];

    public string $view = 'island-counter';

    #[Computed]
    public function double(): int
    {
        return $this->count * 2;
    }

    public function increment(): void
    {
        $this->count++;
    }

    public function flash(): void
    {
        $this->renderIsland('stats', with: ['flash' => 'boom']);
    }

    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make($this->view);
    }
}
