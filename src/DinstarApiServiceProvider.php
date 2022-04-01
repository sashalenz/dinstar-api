<?php

namespace Sashalenz\DinstarApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class DinstarApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('dinstar-api')
            ->hasConfigFile();
    }
}
