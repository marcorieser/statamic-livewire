<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests\Fixtures;

use Livewire\Attributes\Computed;
use Livewire\Component;

class BladeIslandCounter extends Component
{
    public int $count = 0;

    #[Computed]
    public function double(): int
    {
        return $this->count * 2;
    }

    public function render(): string
    {
        return <<<'BLADE'
        <div>
            @island('stats')
                <span>inside: {{ $count }}</span>
                <span>injected: {{ $double ?? 'no' }}</span>
            @endisland
        </div>
        BLADE;
    }
}
