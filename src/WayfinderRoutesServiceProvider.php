<?php

namespace NckRtl\WayfinderRoutes;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use NckRtl\WayfinderRoutes\Commands\WayfinderRoutesCommand;

class WayfinderRoutesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('wayfinder-routes')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_wayfinder_routes_table')
            ->hasCommand(WayfinderRoutesCommand::class);
    }
}
