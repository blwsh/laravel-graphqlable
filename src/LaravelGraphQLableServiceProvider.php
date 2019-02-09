<?php

namespace UniBen\LaravelGraphQLable;

use function compact;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use function is_a;
use function is_array;
use function is_string;
use function request;
use UniBen\LaravelGraphQLable\Exceptions\InvalidGraphQLTypeException;
use function view;

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
         * @param        $returnType
         * @param string $queryType
         * @param array  $graphQlTypeArgs
         * @param bool   $isList
         * @param string $graphQLName
         *
         * @var $this Route
         *
         * @return $this
         */
        Route::macro(
            'graphQL', function ($returnType, $graphQlType = 'query', $graphQlTypeArgs = [], $isList = true, $graphQLName = null) {
            if (!(is_string($returnType) || is_array($returnType))) throw new InvalidGraphQLReturnTypeException();

            if (!in_array($graphQlType, ['query', 'mutation'])) throw new InvalidGraphQLTypeException();

            $this->graphQl = true;
            $this->graphQlData = compact('returnType', 'graphQlType', 'graphQlTypeArgs', 'isList', 'graphQLName');

            return $this;
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
