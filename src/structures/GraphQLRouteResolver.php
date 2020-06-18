<?php

namespace UniBen\LaravelGraphQLable\Structures;


use GraphQL\Error\Error;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use GraphQL\Error\FormattedError;
use Illuminate\Validation\ValidationException;
use UniBen\LaravelGraphQLable\Exceptions\InvalidGraphQLTypeException;

/**
 * Class GraphQLRouteResolver
 * @package UniBen\LaravelGraphQLable\Structures
 *
 * @property \Illuminate\Routing\Route $model
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

        try {
            $result = $this->model->run();
        } catch (ValidationException $e) {
            throw new \UniBen\LaravelGraphQLable\Exceptions\ValidationException($e->validator, null);
        }

        if ($result instanceof JsonResponse) {
            return $result->getData();
        } else {
            return $result;
        }
    }
}
