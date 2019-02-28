<?php

namespace UniBen\LaravelGraphQLable\Controllers;

use Exception;
use GraphQL\Error\FormattedError;
use GraphQL\Server\StandardServer;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Route as Router;
use UniBen\LaravelGraphQLable\Structures\GraphQLSchemaBuilder;
use UniBen\LaravelGraphQLable\Traits\GraphQLQueryableTrait;

/**
 * Class GraphQLController
 * @package UniBen\LaravelGraphQLable\controllers
 */
class GraphQLController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function view() {
        $builder = new GraphQLSchemaBuilder($this->getGraphQLTypesFromModels(), $this->getGraphQLTypesFromRoutes());

        $schema = $builder->getSchema();

        try {
            $server = new StandardServer([
                'schema' => $schema,
                'debug' => config('app.debug', false)
            ]);

            $server->handleRequest(null, true);
        } catch (Exception $e) {
            return ['errors' => FormattedError::createFromException($e, true)];
        }
    }

    /**
     * @return array|\Symfony\Component\Finder\SplFileInfo[]
     */
    function getGraphQLTypesFromModels()
    {
        $classes = File::allFiles(app_path());
        foreach ($classes as $class) {
            $class->classname = str_replace(
                [app_path(), '/', '.php'],
                ['App', '\\', ''],
                $class->getRealPath()
            );
        }

        $classes = collect($classes)
            ->filter(function($model) {
                return in_array(GraphQLQueryableTrait::class, class_uses($model->classname));
            })
            ->toArray();

        return $classes;
    }

    /**
     * @return array
     */
    function getGraphQLTypesFromRoutes() {
        $graphQlRoutes = collect(Router::getRoutes())
            ->filter(function($route) {
                return isset($route->graphQl) && $route->graphQl;
            })
            ->toArray();

        return $graphQlRoutes;
    }
}
