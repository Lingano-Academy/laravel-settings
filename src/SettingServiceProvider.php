<?php

namespace Vocabia\LaravelSettings;

use DevoriaX\LaravelSettings\Services\SettingService;
use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/settings.php', 'settings'
        );

        $this->app->singleton('setting', function ($app) {
            return new SettingService();
        });
    }

    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/settings.php' => $this->app->configPath('settings.php'),
            ],
            'settings-config'
        );

        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');

        $this->publishes(
            [
                __DIR__ . '/Database/Migrations/' => $this->app->databasePath('migrations')
            ],
            'settings-migrations'
        );
    }
}