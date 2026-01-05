<?php

namespace Vocabia\LaravelSettings;

use Vocabia\LaravelSettings\Services\VocabiaSettingService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/vocabia_settings.php', 'settings'
        );

        $this->app->singleton('setting', function ($app) {
            return new VocabiaSettingService();
        });
    }

    public function boot(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/vocabia_settings.php' => $this->app->configPath('vocabia_settings.php'),
            ],
            'settings-config'
        );

        if (Config::get('settings.load_migrations', true)) {
            $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        }

        $this->publishes(
            [
                __DIR__ . '/Database/Migrations/' => $this->app->databasePath('migrations')
            ],
            'settings-migrations'
        );
    }
}