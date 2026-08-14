<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Livewire;
use MarcoRieser\Livewire\Tests\Fixtures\PaginatedList;

beforeEach(function (): void {
    Livewire::component('paginated-list', PaginatedList::class);
});

it('renders the first page with pagination links', function (): void {
    $html = antlers('{{ livewire:paginated-list }}');

    expect($html)
        ->toContain('item: 1')
        ->toContain('item: 3')
        ->not->toContain('item: 4')
        ->toContain('nextPage');
});

it('paginates through livewire updates', function (): void {
    Livewire::test(PaginatedList::class)
        ->assertSee('item: 1')
        ->assertDontSee('item: 4')
        ->call('nextPage')
        ->assertSee('item: 4')
        ->assertDontSee('item: 1')
        ->call('previousPage')
        ->assertSee('item: 1');
});

it('supports a custom links key for multiple paginators', function (): void {
    $paginator = new LengthAwarePaginator(collect([1, 2]), 2, 2, 1);

    $data = (new PaginatedList)->withPagination('numbers', $paginator, 'numbers_links');

    expect($data)->toHaveKeys(['numbers', 'numbers_links'])
        ->and($data['numbers'])->toBe([1, 2]);
});
