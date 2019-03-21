<?php

namespace UniBen\LaravelGraphQLable\Structures;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class GraphQLRouteResolver
 * @package UniBen\LaravelGraphQLable\Structures
 */
class GraphQLModelMutationResolver extends GraphQLResolver
{
    /**
     * @var
     */
    protected $operation;

    /**
     * GraphQLRouteResolver constructor.
     *
     * @param             $model
     * @param             $operation
     * @param             $value
     * @param             $args
     * @param             $context
     * @param ResolveInfo $info
     */
    public function __construct($model, $operation, $value, $args, $context, ResolveInfo $info) {
        $this->operation = $operation;

        parent::__construct($model, $value, $args, $context, $info);
    }

    /**
     * @return mixed This method should return data that can be resolved to the
     *               type of the graphQL resource requested.
     */
    public function resolve()
    {
        return $this->model->{$this->operation}(...$this->args);
    }

}