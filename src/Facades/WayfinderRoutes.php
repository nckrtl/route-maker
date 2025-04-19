<?php

namespace NckRtl\WayfinderRoutes\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NckRtl\WayfinderRoutes\WayfinderRoutes
 */
class WayfinderRoutes extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \NckRtl\WayfinderRoutes\WayfinderRoutes::class;
    }
}
