<?php

/**
 * Laravel Artisan Installer Service Provider
 * 
 * Registers the installer package with Laravel's service container and
 * handles the package's configuration and command registration.
 */
namespace DarshPhpDev\LaravelArtisanInstaller;

use Illuminate\Support\ServiceProvider;

class InstallerServiceProvider extends ServiceProvider {
    public function boot() {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\InstallCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/config/installer.php' => config_path('installer.php'),
        ], 'installer-config');
    }

    public function register() {
        $this->mergeConfigFrom(
            __DIR__.'/config/installer.php', 'installer'
        );
    }
}