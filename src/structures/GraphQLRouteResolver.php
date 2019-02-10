<?php

namespace UniBen\LaravelGraphQLable\Structures;

use GraphQL\Type\Definition\ResolveInfo;

/**
 * Class GraphQLRouteResolver
 * @package UniBen\LaravelGraphQLable\Structures
 */
class GraphQLRouteResolver extends GraphQLResolver
{
    /**
     * @return mixed This method should return data that can be resolved to the
     *               type of the graphQL resource requested.
     */
    public function resolve()
    {
        $this->model->parameters = []; request()->merge($this->args);
        return $this->model->run();
    }

}