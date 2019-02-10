<?php

namespace UniBen\LaravelGraphQLable\Structures;

use Exception;
use GraphQL\Type\Schema;
use Illuminate\Routing\Route;
use GraphQL\Error\FormattedError;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use UniBen\LaravelGraphQLable\Traits\GraphQLQueryableTrait;
use UniBen\LaravelGraphQLable\Exceptions\GraphQLControllerMethodException;

class GraphQLSchemaBuilder
{
    protected $schema;

    protected $models;

    protected $routes;

    protected $queries = [];

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
            return ['errors' => FormattedError::createFromException($e, true)];
        }

        return $this->schema = $schema;
    }

    public function getSchema() {
        return $this->schema;
    }

    protected function handleModelTypes() {
        foreach ($this->models as $model) {
            $model = new $model->classname;

            $this->handleModelTypeQuery($model);
            $this->handleModelTypemutations($model);
        }
    }

    /**
     * @param Model|GraphQLQueryableTrait $model
     */
    protected function handleModelTypeQuery($model) {
        $graphQLType = $model::generateType();

        // Add the generated model type
        $this->queries[$graphQLType->name] = [
            'name' => $graphQLType->name,
            'type' => Type::listOf($graphQLType),
            'resolve' => function($value, $args, $context, ResolveInfo $info) use ($model) {
                $query = $model::query();

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
    }

    /**
     * @param Model|GraphQLQueryableTrait $model
     */
    protected function handleModelTypeMutations($model) {
        $graphQLType = $model::generateType();

        foreach ($model->getMutatables() as $operation) {
            $mutations[camel_case("$operation $graphQLType->name")] = [
                'args' => $model::getMappedGraphQLFields(),
                'type' => $graphQLType,
                'resolve' => function($rootValue, ...$args) use ($model, $operation) {
                    return $model->$operation(...$args);
                }
            ];
        }
    }

    /**
     * Builds queries and mutations from routes which use the
     * GraphQLQueryableTrait.
     */
    protected function handleRouteTypes() {
        foreach ($this->routes as $route) {
            $this->{str_plural($route->graphQlData['graphQlType'])}[$this->getRouteGraphQLName($route)] = [
                'name' => $this->getRouteGraphQLName($route),
                'args' => $route->graphQlData['graphQlTypeArgs'],
                'type' => $route->graphQlData['isList'] ? Type::listOf(($route->graphQlData['returnType'])::generateType()) : $route->graphQlData['returnType']::generateType(),
                'resolve' => function($rootValue, $args) use ($route) {
                    $route->parameters = []; request()->merge($args);
                    return $route->run();
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
                return camel_case(str_replace('Controller', '', class_basename($route->getController())) . ' ' . $route->getActionMethod() . ' ' . $route->graphQlData['graphQlType']);
            } else {
                throw new GraphQLControllerMethodException("A graphQL route that uses a closure must have a name.");
            }
        }
    }
}