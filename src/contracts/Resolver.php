<?php

namespace UniBen\LaravelGraphQLable\Contracts;

interface Resolver
{
    /**
     * @return mixed This method should return data that can be resolved to the
     *               type of the graphQL resource requested.
     */
    public function resolve();
}