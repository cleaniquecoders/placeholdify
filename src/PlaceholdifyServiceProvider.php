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
            ->hasCommand(PlaceholdifyCommand::class);
    }

    public function packageBooted(): void
    {
        // Register singleton for PlaceholderHandler
        $this->app->singleton(PlaceholderHandler::class, function ($app) {
            return new PlaceholderHandler;
        });

        // Register facade alias
        $this->app->alias(PlaceholderHandler::class, 'placeholdify');
    }
}
