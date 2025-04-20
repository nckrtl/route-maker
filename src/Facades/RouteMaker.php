<?php

namespace NckRtl\RouteMaker\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \NckRtl\RouteMaker\RouteMaker
 */
class RouteMaker extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \NckRtl\RouteMaker\RouteMaker::class;
    }
}
