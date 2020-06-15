<?php

namespace UniBen\LaravelGraphQLable\Controllers;

use Exception;
use GraphQL\Error\FormattedError;
use GraphQL\Server\StandardServer;
use App\Http\Controllers\Controller;
use UniBen\LaravelGraphQLable\Structures\GraphQLSchemaBuilder;
use UniBen\LaravelGraphQLable\Traits\GraphQLQueryableTrait;
use UniBen\LaravelGraphQLable\utils\DiscoverGraphQLModels;
use UniBen\LaravelGraphQLable\utils\DiscoverGraphQLRoutes;

/**
 * Class GraphQLController
 * @package UniBen\LaravelGraphQLable\controllers
 */
class GraphQLController extends Controller
{
    protected $schema;

    public function __construct()
    {
        $this->schema = (new GraphQLSchemaBuilder(
            (new DiscoverGraphQLModels())->get(),
            (new DiscoverGraphQLRoutes())->get()
        ))->getSchema();
    }

    /**
     * @throws \Throwable
     */
    public function view()
    {
        try {
            $server = new StandardServer([
                'schema' => $this->schema,
                'debug' => config('app.debug', false)
            ]);

            $server->handleRequest(null, true)->toArray(true);
        } catch (Exception $e) {
            return ['errors' => FormattedError::createFromException($e, true)];
        }
    }
}
