<?php

namespace MarcoRieser\Livewire\Helpers;

use Statamic\View\Antlers\Language\Parser\PathParser;
use Statamic\View\Antlers\Language\Runtime\PathDataManager;

/**
 * @deprecated
 */
class DataFetcher
{
    public static function getValue(string $path, array $context)
    {
        $dataManager = app(PathDataManager::class)
            ->setIsPaired(false)
            ->setReduceFinal(false);

        $variable = app(PathParser::class)->parse($path);

        [$resolved, $result] = $dataManager->getDataWithExistence($variable, $context);

        if (! $resolved) {
            return null;
        }

        return $result;
    }
}
