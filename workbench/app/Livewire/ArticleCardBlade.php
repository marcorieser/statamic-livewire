<?php

declare(strict_types=1);

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;

class ArticleCardBlade extends ArticleCard
{
    public function render(): View
    {
        return view()->make('livewire.article-card-blade');
    }
}
