<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire;

use Illuminate\Contracts\Pagination\Paginator;

trait WithPagination
{
    use \Livewire\WithPagination;

    /**
     * Prepare a paginator for an Antlers component view: the items become a
     * loopable variable and the rendered pagination links a separate one.
     * Pass a custom links key when using multiple paginators.
     *
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Paginator<TKey, TValue>  $paginator
     * @return array<string, mixed>
     */
    public function withPagination(string $key, Paginator $paginator, string $linksKey = 'links'): array
    {
        return [
            $key => $paginator->items(),
            $linksKey => $paginator->render(),
        ];
    }
}
