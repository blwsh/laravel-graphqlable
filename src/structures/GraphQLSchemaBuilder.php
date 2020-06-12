<?php

namespace UniBen\LaravelGraphQLable\Structures;

use DB;
use Exception;
use GraphQL\Type\Schema;
use Illuminate\Support\Str;
use Illuminate\Routing\Route;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ObjectType;
use UniBen\LaravelGraphQLable\Traits\GraphQLQueryableTrait;
use UniBen\LaravelGraphQLable\Exceptions\GraphQLControllerMethodException;

/**
 * Class GraphQLSchemaBuilder
 * @package UniBen\LaravelGraphQLable\Structures
 */
class GraphQLSchemaBuilder
{
    /**
     * The generated schema.
     *
     * @var Schema
     */
    protected $schema;

    /**
     * An array of models to be handled.
     *
     * @var array
     */
    protected $models;

    /**
     * An array of routes to be handled.
     *
     * @var array
     */
    protected $routes;

    /**
     * Store of queries found during schema generation.
     *
     * @var array
     */
    protected $queries = [];

    /**
     * Store of mutations found during schema generation.
     *
     * @var array
     */
    protected $mutations = [];

    /**
     * GraphQLSchemaBuilder constructor.
     *
     * @param array $models
     * @param array $routes
     *
     * @throws \Throwable
     */
    public function __construct(array $models, array $routes) {
        $this->models = $models;
        $this->routes = $routes;

        $this->handleModelTypes();
        $this->handleRouteTypes();

        // Define schema
        $schema = new Schema([
            'query' => ($this->queries ? new ObjectType(['name' => 'query', 'fields' => $this->queries]) : null),
            'mutation' => ($this->mutations ? new ObjectType(['name' => 'mutation', 'fields' => $this->mutations]) : null)
        ]);

        // Check the schema
        try {
            $schema->assertValid();
        } catch (Exception $e) {
            throw $e;
        }

        return $this->schema = $schema;
    }

    /**
     * @return Schema
     */
    public function getSchema() {
        return $this->schema;
    }

    /**
     * Searches for types, queries and mutations and adds them to relative array
     * for later consumption when building Schema object.
     *
     * @return void
     */
    protected function handleModelTypes() {
        foreach ($this->models as $model) {
            $model = new $model->classname;

            // Handle the model queries and mutations
            $this->handleModelTypeQuery($model);
            $this->handleModelTypeMutations($model);
        }
    }

    /**
     * Builds queries from model attributes.
     *
     * @param Model|GraphQLQueryableTrait $model
     *
     * @return void
     */
    protected function handleModelTypeQuery($model) {
        $graphQLType = $model::getGraphQLType();

        $this->queries[Str::plural($graphQLType->name)] = [
            'name' => Str::plural($graphQLType->name),
            'type' => Type::listOf($graphQLType),
            'resolve' => function(...$args) use ($model) {
                 return (new GraphQLModelQueryResolver($model, ...$args))->resolve();
            }
        ];
    }

    /**
     * Builds schema from model attributes and methods.
     *
     * @param Model|GraphQLQueryableTrait $model
     *
     * @return void
     */
    protected function handleModelTypeMutations($model) {
        $graphQLType = $model::getGraphQLType();

        foreach ($model->getMutatables() as $operation) {
            $mutations[Str::camel("$operation " . $graphQLType->name)] = [
                'args' => $model::getMappedGraphQLFields(),
                'type' => $graphQLType,
                'resolve' => function(...$args) use ($model, $operation) {
                    return (new GraphQLModelMutationResolver($model, $operation, ...$args))->resolve();
                }
            ];
        }
    }

    /**
     * Builds queries and mutations from routes which use the
     * GraphQLQueryableTrait.
     *
     * @return void
     *
     * @throws GraphQLControllerMethodException
     */
    protected function handleRouteTypes() {
        foreach ($this->routes as $route) {
            $name = $this->getRouteGraphQLName($route);

            $this->{Str::plural($route->graphQlData['graphQlType'])}[$name] = [
                'name' => $name,
                'args' => $route->graphQlData['graphQlTypeArgs'],
                'type' => $route->graphQlData['isList'] ? Type::listOf(($route->graphQlData['returnType'])) : $route->graphQlData['returnType'],
                'resolve' => function(...$args) use ($route) {
                    return (new GraphQLRouteResolver($route, ...$args))->resolve();
                }
            ];
        }
    }

    /**
     * If a route name is not specified the method will attempt to build a name
     * from the route controller. If the route uses a closure and there is no
     * name specified the method will throw an exception.
     *
     * @param Route $route
     *
     * @return null|string
     *
     * @throws GraphQLControllerMethodException
     */
    protected function getRouteGraphQLName(Route $route) {
        $name = null;

        if ($route->getName()) {
            return $route->getName();
        } else {
            if (is_string($route->action['uses'])) {
                return Str::camel(str_replace('Controller', '', class_basename($route->getController())) . ' ' . $route->getActionMethod() . ' ' . $route->graphQlData['graphQlType']);
            } else {
                throw new GraphQLControllerMethodException("A graphQL route that uses a closure must have a name.");
            }
        }
    }
}
