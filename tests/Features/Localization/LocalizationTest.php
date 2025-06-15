<?php

namespace MarcoRieser\Livewire\Tests\Features\Localization;

use MarcoRieser\Livewire\Testing\Concerns\CanManipulateAddonConfig;
use MarcoRieser\Livewire\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;

class LocalizationTest extends TestCase
{
    use CanManipulateAddonConfig;
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function localization_is_enabled_by_default()
    {
        $this->assertTrue(config('statamic-livewire.localization'));
    }
}
