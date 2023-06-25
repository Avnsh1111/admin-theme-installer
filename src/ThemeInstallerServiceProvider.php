<?php

namespace Avnsh1111\ThemeInstaller;

use Avnsh1111\ThemeInstaller\Commands\InstallTheme;
use Illuminate\Support\ServiceProvider;

class ThemeInstallerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Loads routes, views, migrations
        // $this->loadRoutesFrom(__DIR__.'/Themes/'.$this->loadTheme().'/routes.php');
        // $this->loadViewsFrom(__DIR__.'/Themes/'.$this->loadTheme().'/Resources/views', 'my-laravel-package');
        // $this->loadMigrationsFrom(__DIR__.'/Themes/'.$this->loadTheme().'/Migrations');
    }

    public function register()
    {
        // Publish config and assets
        // $this->publishes([
        //     __DIR__.'/../config/config.php' => config_path('my-laravel-package.php'),
        //     __DIR__.'/Themes/'.$this->loadTheme().'/Assets' => public_path('vendor/my-laravel-package'),
        // ]);

        // Register your command
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallTheme::class,
            ]);
        }
    }

    protected function loadTheme()
    {
        // Get theme name from the config or set default theme
        // $theme = config('my-laravel-package.theme', 'Theme1');
        // // Ensure that the theme directory exists, otherwise, use the default theme
        // if (!is_dir(__DIR__."/Themes/$theme")) {
        //     $theme = 'Theme1';
        // }
        // return $theme;
    }
}
