<?php

namespace UniBen\LaravelGraphQLable\controllers;

use Exception;
use GraphQL\Type\Schema;
use GraphQL\Error\FormattedError;
use GraphQL\Type\Definition\Type;
use GraphQL\Server\StandardServer;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Routing\RouteCollection;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Route as Router;
use UniBen\LaravelGraphQLable\Traits\GraphQLQueryableTrait;

class GraphQLController extends Controller
{
    public function view() {
        // Init queries and mutations arrays
        $queries = $mutations = [];

        // Register models
        foreach ($this->getGraphQLSchemaFromModels() as $model) {
            try {
                /**
                 * @var Model|GraphQLQueryableTrait $initModel
                 */
                $initModel = new $model->classname();
                $graphQLType = $initModel->generateType();

                // Add the generated model type
                $queries[$graphQLType->name] = [
                    'name' => $graphQLType->name,
                    'type' => Type::listOf($graphQLType),
                    'resolve' => function($value, $args, $context, ResolveInfo $info) use ($initModel) {
                        return $initModel->newQuery()->select(array_keys($info->getFieldSelection()))->get()->toArray();
                    }
                ];

                foreach ($initModel->getMutatables() as $operation) {
                    $mutations[camel_case("$operation $graphQLType->name")] = [
                        'args' => $initModel->getMappedGraphQLFields(),
                        'type' => $graphQLType,
                        'resolve' => function($rootValue, ...$args) use ($initModel, $operation) {
                            return $initModel->$operation(...$args);
                        }
                    ];
                }
            } catch (Exception $e) {
                $queries['errors'] = [
                    'name' => explode('\\', $model->classname)[0],
                    'type' => Type::string(),
                    'resolve' => function() use ($e) {
                        return $e->getMessage();
                    }
                ];
            }
        }

        // Register controller
        $this->getGraphQLSchemaFromControllers();

        // Define schema
        $schema = new Schema([
            'query' => ($queries ? new ObjectType([
                'name' => 'query',
                'fields' => $queries
            ]) : null),
            'mutation' => ($mutations ? new ObjectType([
                'name' => 'mutation',
                'fields' => $mutations
            ]) : null)
        ]);

        // Check the schema
        try {
            $schema->assertValid();
        } catch (Exception $e) {
            return ['errors' => FormattedError::createFromException($e, true)];
        }

        // Start the server
        try {
            $server = new StandardServer([
                'schema' => $schema,
                'debug' => config('app.debug')
            ]);

            $server->handleRequest(null, true);
        } catch (\Exception $e) {
            return ['errors' => FormattedError::createFromException($e, true)];
        }
    }

    function getGraphQLSchemaFromControllers() {
        /** @var RouteCollection $routes */
        dd(Router::getRoutes());
    }

    function getGraphQLSchemaFromModels()
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
}
