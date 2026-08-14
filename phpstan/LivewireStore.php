<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\PhpStan;

/**
 * Static type for the anonymous store object returned by \Livewire\store().
 * Only used during static analysis; has no runtime counterpart. The method
 * signatures live in phpstan/stubs/livewire.stub.
 */
interface LivewireStore
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value): void;

    public function push(string $key, mixed $value, int|string|null $iKey = null): void;

    public function find(string $key, int|string|null $iKey = null, mixed $default = null): mixed;

    public function has(string $key, int|string|null $iKey = null): bool;

    public function unset(string $key, int|string|null $iKey = null): void;
}
