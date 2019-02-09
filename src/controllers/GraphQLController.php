<?php

namespace UniBen\LaravelGraphQLable\controllers;

use Exception;
use GraphQL\Type\Schema;
use Illuminate\Routing\Route;
use GraphQL\Error\FormattedError;
use GraphQL\Type\Definition\Type;
use GraphQL\Server\StandardServer;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Route as Router;
use UniBen\LaravelGraphQLable\Traits\GraphQLQueryableTrait;

/**
 * Class GraphQLController
 * @package UniBen\LaravelGraphQLable\controllers
 */
class GraphQLController extends Controller
{
    /**
     * @return array
     * @throws \Throwable
     */
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
                $graphQLType = $initModel::generateType();

                // Add the generated model type
                $queries[$graphQLType->name] = [
                    'name' => $graphQLType->name,
                    'type' => Type::listOf($graphQLType),
                    'resolve' => function($value, $args, $context, ResolveInfo $info) use ($initModel) {
                        $query = $initModel::query();

                        $query->addSelect('id');

                        foreach ($info->getFieldSelection(2) as $field => $value) {
                            if (is_array($value)) {
                                $query->with([$field => function($query) use ($value) {
                                    $query->addSelect(array_keys($value));
                                }]);
                            } else {
                                $query->addSelect($field);
                            }
                        }

                        return $query->get()->toArray();
                    }
                ];

                foreach ($initModel->getMutatables() as $operation) {
                    $mutations[camel_case("$operation $graphQLType->name")] = [
                        'args' => self::getMappedGraphQLFields(),
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

        // Combine queries and mutations with ones found in controllers
        $queries = array_merge($queries, $this->getGraphQLQueryTypesFromControllers());
        $mutations = array_merge($mutations, $this->getGraphQLMutationTypesFromControllers());

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
                'debug' => config('app.debug', false)
            ]);

            $server->handleRequest(null, true);
        } catch (\Exception $e) {
            return ['errors' => FormattedError::createFromException($e, true)];
        }
    }

    /**
     * @param null $type
     *
     * @return array
     */
    function getGraphQLTypesFromControllers($type = null) {
        $graphQlRoutes = collect(Router::getRoutes())->filter(function($route) use ($type) {
            if (isset($route->graphQl) && $route->graphQl) {
                if ($type != null) {
                    return isset($route->graphQlData['graphQlType']) && $route->graphQlData['graphQlType'] == $type;
                }

                return true;
            }

            return false;
        });

        $data = $type ? [$type => []] : ['query' => [], 'mutation' => []];

        $graphQlRoutes->each(function($route) use (&$data) {
            /** @var Route $route */
            $fallbackName = camel_case(str_replace('Controller', '', class_basename($route->getController())) . ' ' . $route->getActionMethod() . ' ' . $route->graphQlData['graphQlType']);
            $data[$route->graphQlData['graphQlType']][$route->graphQlData['graphQLName '] ?? $fallbackName] = [
                'name' => $route->graphQlData['graphQLName'] ?? $fallbackName,
                'args' => $route->graphQlData['graphQlTypeArgs'],
                'type' => $route->graphQlData['isList'] ? Type::listOf(($route->graphQlData['returnType'])::generateType()) : $route->graphQlData['returnType']::generateType(),
                'resolve' => function($rootValue, $args) use ($route) {
                    request()->merge($args);
                    return App::call($route->getActionName(), $args);
                }
            ];
        });

        return $type ? $data[$type] : $data;
    }

    /**
     * @return array
     */
    function getGraphQLQueryTypesFromControllers() {
        return $this->getGraphQLTypesFromControllers('query');
    }

    /**
     * @return array
     */
    function getGraphQLMutationTypesFromControllers() {
        return $this->getGraphQLTypesFromControllers('mutation');
    }

    /**
     * @return array|\Symfony\Component\Finder\SplFileInfo[]
     */
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
