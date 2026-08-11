<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use MarcoRieser\Livewire\WithPagination;
use Statamic\Facades\Entry;
use Statamic\Facades\Site;

class ArticleList extends Component
{
    use WithPagination;

    /**
     * @return array<string, mixed>
     */
    protected function articles(): array
    {
        $paginator = Entry::query()
            ->where('collection', 'articles')
            ->where('site', Site::current()->handle())
            ->orderBy('title')
            ->paginate(3);

        return $this->withPagination('articles', $paginator);
    }

    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('livewire.article-list')->with($this->articles());
    }
}
