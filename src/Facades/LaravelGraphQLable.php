<?php

namespace UniBen\LaravelGraphQLable\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelGraphQLable extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravelgraphqlable';
    }
}
