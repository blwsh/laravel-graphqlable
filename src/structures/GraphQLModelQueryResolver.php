<?php

namespace UniBen\LaravelGraphQLable\Structures;

use function array_filter;
use function array_flatten;
use function array_keys;
use function dd;
use Exception;
use Illuminate\Database\Eloquent\Relations\Relation;
use function implode;
use function is_array;
use function print_r;
use function response;

class GraphQLModelQueryResolver extends GraphQLResolver
{
    protected static $with;

    /**
     * @param Relation    $query
     * @param null        $fields
     * @param string|null $parent
     *
     * @return mixed This method should return data that can be resolved to the
     *               type of the graphQL resource requested.
     */
    public function resolve($query = null, $fields = null, string $parent = null)
    {
        $query = $query ?? $this->model::query()->select('id');
        $fields = $fields ?? $this->info->getFieldSelection(2);

        foreach ($fields as $field => $value) {
            if (is_array($value)) {
                $field = ($parent ? $parent . '.' : null) . $field;
            self::$with[] = [$field => array_filter($value, function($v) { return !is_array($v); })];
                $this->resolve($query, $value, $field);
            } else {
                $query->addSelect($field);
            }
        }

        dd(self::$with);

        return $query->get();
    }
}