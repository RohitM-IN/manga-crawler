<?php

namespace RohitMIN\MangaCrawler;

use RohitMIN\MangaCrawler\Commands\MangaCrawlerCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MangaCrawlerServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('manga-crawler')
            ->hasConfigFile()
            // ->hasViews()
            // ->hasMigration('create_manga-crawler_table')
            ->hasCommand(MangaCrawlerCommand::class);
    }
}
