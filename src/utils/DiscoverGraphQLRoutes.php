<?php namespace UniBen\LaravelGraphQLable\utils;

use Illuminate\Support\Facades\Route as Router;

/**
 * Class DiscoverGraphQLRoutes
 * @package UniBen\LaravelGraphQLable\utils
 */
class DiscoverGraphQLRoutes
{
    /**
     * @var array
     */
    protected $classes;

    /**
     * DiscoverGraphQLRoutes constructor.
     */
    public function __construct()
    {
        $this->classes = collect(Router::getRoutes())
            ->filter(function($route) {
                return isset($route->graphQl) && $route->graphQl;
            })
            ->toArray();
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->classes;
    }
}
