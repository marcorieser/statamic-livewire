<?php

namespace MarcoRieser\Livewire;

use Illuminate\Support\Facades\Date;
use Livewire\Attributes\Locked;
use Statamic\Facades\Site;
use Statamic\Statamic;

/**
 * In v5, this trait will be replaced by the new localization feature, which will be enabled by default.
 * It currently sits behind a config flag and is still experimental.
 * https://github.com/marcorieser/statamic-livewire/tree/main?tab=readme-ov-file#automatic-localization-handling-experimental
 *
 * @deprecated
 */
trait RestoreCurrentSite
{
    #[Locked]
    public string $siteHandle = '';

    public function mountRestoreCurrentSite(): void
    {
        $this->siteHandle = Site::current()->handle();
    }

    public function hydrateRestoreCurrentSite(): void
    {
        Site::setCurrent($this->siteHandle);

        $site = Site::current();

        setlocale(LC_TIME, $site->locale());
        app()->setLocale($site->lang());
        Date::setToStringFormat(Statamic::dateFormat());
    }
}
