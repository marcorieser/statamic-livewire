<?php

declare(strict_types=1);

namespace MarcoRieser\Livewire\Console\Commands;

use Illuminate\Console\Command;

class LivewireCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'statamic-livewire:placeholder';

    /**
     * The command description.
     */
    protected $description = 'Placeholder Artisan command shipped by the package statamic-livewire.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Livewire placeholder command executed.');

        return self::SUCCESS;
    }
}
