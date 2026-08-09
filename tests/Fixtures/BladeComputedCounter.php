<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Tests\Fixtures;

use Livewire\Attributes\Computed;
use Livewire\Component;

class BladeComputedCounter extends Component
{
    public int $count = 2;

    #[Computed]
    public function double(): int
    {
        return $this->count * 2;
    }

    public function render(): string
    {
        return '<div>injected: {{ $double ?? \'no\' }} native: {{ $this->double }}</div>';
    }
}
