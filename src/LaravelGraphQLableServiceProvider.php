<?php

namespace UniBen\LaravelGraphQLable;

use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use UniBen\LaravelGraphQLable\Exceptions\InvalidGraphQLTypeException;

class LaravelGraphQLableServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }

        /**
         * Route override
         *
         * @var $this Route
         */
        Route::macro('graphQL', function ($type = 'query', $isList = true) {
            if (!in_array($type, ['query', 'mutation'])) throw new InvalidGraphQLTypeException();
            return $this;

            // throw new GraphQLControllerMethodException("This route is for a GraphQL endpoint and can not be accessed.");
        });
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
    }
}
