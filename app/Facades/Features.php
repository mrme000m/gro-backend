<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool isEnabled(string $feature)
 * @method static bool isCategoryEnabled(string $category)
 * @method static array getEnabledFeatures(string $category)
 * @method static array getNavigationItems()
 * @method static array getAllFeatures()
 * @method static void clearCache()
 * @method static bool updateFeatures(array $features)
 */
class Features extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'features';
    }
}
