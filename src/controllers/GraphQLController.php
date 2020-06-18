<?php

namespace UniBen\LaravelGraphQLable\Controllers;

use Exception;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use GraphQL\Server\ServerConfig;
use GraphQL\Server\StandardServer;
use App\Http\Controllers\Controller;
use UniBen\LaravelGraphQLable\Structures\GraphQLSchemaBuilder;
use UniBen\LaravelGraphQLable\Traits\GraphQLQueryableTrait;
use UniBen\LaravelGraphQLable\utils\DiscoverGraphQLModels;
use UniBen\LaravelGraphQLable\utils\DiscoverGraphQLRoutes;
use GraphQL\Error\Debug;

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
        $debug = config('app.debug', false) ? Debug::INCLUDE_DEBUG_MESSAGE | Debug::INCLUDE_TRACE : false;

        try {
            $config = ServerConfig::create([
                'schema' => $this->schema,
                'debug' => $debug
            ]);

            $server = new StandardServer($config);

            $server->handleRequest(null, true)->toArray();
        } catch (\Throwable $e) {
            return ['errors' => FormattedError::createFromException($e, $debug)];
        }
    }
}
