<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests\Fixtures;

use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use MarcoRieser\Livewire\WithPagination;

class PaginatedList extends Component
{
    use WithPagination;

    /**
     * @return array<string, mixed>
     */
    protected function numbers(): array
    {
        $items = collect(range(1, 9));

        $page = is_int($page = $this->getPage()) ? $page : 1;

        $paginator = new LengthAwarePaginator(
            $items->forPage($page, 3)->values(),
            $items->count(),
            3,
            $page,
        );

        return $this->withPagination('numbers', $paginator);
    }

    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('paginated-list')->with($this->numbers());
    }
}
