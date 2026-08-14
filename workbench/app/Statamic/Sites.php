<?php

declare(strict_types=1);

namespace Workbench\App\Statamic;

use Statamic\Sites\Sites as StatamicSites;

use function Orchestra\Testbench\workbench_path;

/**
 * Statamic hard-codes the sites file at resource_path('sites.yaml'), which
 * lives in the disposable Testbench skeleton. Point it at the workbench
 * instead so sites (and any CP edits to them) survive a `composer clear`.
 */
class Sites extends StatamicSites
{
    public function path(): string
    {
        return workbench_path('resources/sites.yaml');
    }
}
