<?php

namespace UniBen\LaravelGraphqlable\Facades;

use Illuminate\Support\Facades\Facade;

class LaravelGraphqlable extends Facade
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
