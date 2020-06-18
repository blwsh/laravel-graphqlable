<?php

namespace UniBen\LaravelGraphQLable\Structures;

use GraphQL\Type\Definition\ResolveInfo;
use UniBen\LaravelGraphQLable\Contracts\Resolver;

/**
 * Class GraphQLResolver
 * @package UniBen\LaravelGraphQLable\Structures
 */
abstract class GraphQLResolver implements Resolver
{
    /**
     * @var
     */
    protected $model;

    /**
     * @var mixed
     */
    protected $value;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var mixed
     */
    protected $context;

    /**
     * @var ResolveInfo
     */
    protected $info;

    /**
     * GraphQLTypeResolver constructor.
     *
     * @param             $model
     * @param             $value
     * @param             $args
     * @param             $context
     * @param ResolveInfo $info
     */
    public function __construct($model, $value, $args, $context, ResolveInfo $info) {
        $this->model = $model;
        $this->value = $value;
        $this->args = $args;
        $this->context = $context;
        $this->info = $info;
    }
}
