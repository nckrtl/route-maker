<?php

namespace NckRtl\RouteMaker;

use NckRtl\RouteMaker\Commands\RouteMakerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class RouteMakerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('route-maker')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_wayfinder_routes_table')
            ->hasCommand(RouteMakerCommand::class);
    }
}
