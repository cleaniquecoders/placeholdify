<?php

namespace CleaniqueCoders\Placeholdify;

use CleaniqueCoders\Placeholdify\Commands\PlaceholdifyCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PlaceholdifyServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('placeholdify')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_placeholdify_table')
            ->hasCommand(PlaceholdifyCommand::class);
    }
}
