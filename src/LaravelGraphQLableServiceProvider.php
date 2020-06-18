<?php

namespace UniBen\LaravelGraphQLable;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use Illuminate\Routing\Route;
use Illuminate\Support\ServiceProvider;
use ReflectionClass;
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
         * @param        $returnType
         * @param string $queryType
         * @param array  $graphQlTypeArgs
         * @param bool   $isList
         *
         * @var $this Route
         *
         * @return $this
         */
        Route::macro('graphQL', function ($returnType, string $graphQlType = 'query', array $graphQlTypeArgs = [], bool  $isList = true) {
            if (!(is_string($returnType) || is_array($returnType))) {
                throw new InvalidGraphQLReturnTypeException();
            }

            if (!in_array($graphQlType, ['query', 'mutation'])) {
                throw new InvalidGraphQLTypeException();
            }

            $reflection = new ReflectionClass($returnType);

            if ($reflection->isSubclassOf(Type::class)) {
                $returnType = $reflection->newInstance();
            } else {
                $returnType = $returnType::getGraphQLType();
            }

            $this->graphQl = true;
            $this->graphQlName = null;
            $this->graphQlData = compact('returnType', 'graphQlType', 'graphQlTypeArgs', 'isList');

            return $this;
        });

        Route::macro('graphQLName', function(string $name = null) {
            $this->graphQLName = $name;
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
