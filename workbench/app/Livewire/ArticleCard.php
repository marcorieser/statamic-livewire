<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Statamic\Entries\Entry as EntryContract;
use Statamic\Facades\Entry;

class ArticleCard extends Component
{
    public string $article;

    #[Computed]
    public function entry(): ?EntryContract
    {
        return Entry::find($this->article);
    }

    public function render(): View
    {
        // larastan's view-string check only discovers .blade.php files, so the
        // antlers view name has to go through the factory instead of view().
        return view()->make('livewire.article-card');
    }
}
