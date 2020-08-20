<?php

namespace Jeremynikolic\LaravelRevision;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jeremynikolic\LaravelRevision\Skeleton\SkeletonClass
 */
class LaravelRevisionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-revision';
    }
}
