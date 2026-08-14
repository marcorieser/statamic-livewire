<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests\Fixtures;

use Livewire\Component;

class Counter extends Component
{
    public int $count = 0;

    public string $label = 'counter';

    public function render(): string
    {
        return '<div>{{ $label }}: {{ $count }}</div>';
    }
}
