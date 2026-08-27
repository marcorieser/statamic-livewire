<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;

class ArticleListBlade extends ArticleList
{
    public function render(): View
    {
        return view()->make('livewire.article-list-blade')->with($this->articles());
    }
}
