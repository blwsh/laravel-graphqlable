<?php

namespace UniBen\LaravelGraphQLable\Structures;


use function array_values;
use function request;

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
        $this->model->parameters = $this->args; request()->merge($this->args);
        return $this->model->run();
    }

}