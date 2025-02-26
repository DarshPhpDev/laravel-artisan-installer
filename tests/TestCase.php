<?php

namespace DarshPhpDev\LaravelArtisanInstaller\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use DarshPhpDev\LaravelArtisanInstaller\InstallerServiceProvider;

class TestCase extends BaseTestCase {
    protected function getPackageProviders($app) {
        return [
            InstallerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app) {
        // Set up the testing environment
    }
}