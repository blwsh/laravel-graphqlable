<?php

namespace UniBen\LaravelGraphQLable\Structures;

use App\Portfolio;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;

class GraphQLModelQueryResolver extends GraphQLResolver
{
    protected $with;

    /**
     * @param Relation    $query
     * @param null        $fields
     * @param string|null $parent
     *
     * @return mixed This method should return data that can be resolved to the
     *               type of the graphQL resource requested.
     */
    public function resolve(&$query = null, array $fields = null, string $parent = null)
    {
        $query = $query ?? $this->model::query()->select($this->model->getKeyName());
        $fields = $fields ?? $this->info->getFieldSelection(10);

        foreach ($fields as $field => $value) {
            if (is_array($value)) {
                $parentRelation = ($parent ? $parent . '.' : '') . $field;

                $query->with([$parentRelation => function(Relation $query) use ($fields, $field) {
                    $fields = array_keys(array_filter(Arr::get($fields, $field), function($value) {
                        return !is_array($value);
                    }));

                    $query->addSelect($query->getModel()->getQualifiedKeyName());

                    if (method_exists($query, 'getExistenceCompareKey')) {
                        $query->addSelect($query->getExistenceCompareKey());
                    }

                    foreach ($fields as $field) $query->addSelect($field);
                }]);

                $this->resolve($query, $value, $parentRelation);
            } else if ($field == '__typename') {
                // let the controller deal with this.
            } else if (!$parent) {
                $query->addSelect($field);
            }
        }

         return $query->get();
    }
}
