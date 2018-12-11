<?php

namespace UniBen\LaravelGraphQLable;

use Illuminate\Support\ServiceProvider;

class LaravelGraphQLableServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'uniben');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'uniben');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravelgraphqlable.php', 'laravelgraphqlable');

        // Register the service the package provides.
        $this->app->singleton('laravelgraphqlable', function ($app) {
            return new LaravelGraphQLable;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laravelgraphqlable'];
    }
    
    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole()
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/laravelgraphqlable.php' => config_path('laravelgraphqlable.php'),
        ], 'laravelgraphqlable.config');

        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/uniben'),
        ], 'laravelgraphqlable.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/uniben'),
        ], 'laravelgraphqlable.views');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/uniben'),
        ], 'laravelgraphqlable.views');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
