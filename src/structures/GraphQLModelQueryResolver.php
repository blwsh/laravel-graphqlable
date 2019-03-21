<?php

namespace UniBen\LaravelGraphQLable\Structures;

use Illuminate\Database\Eloquent\Relations\Relation;

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
    public function resolve($query = null, $fields = null, string $parent = null)
    {
        $query = $query ?? $this->model::query()->select('id');
        $fields = $fields ?? $this->info->getFieldSelection(2);

        foreach ($fields as $field => $value) {
            if (is_array($value)) {
                $query->with($field);
            } else if ($field == '__typename') {
                // let the controller deal with this.
            } else {
                $query->addSelect($field);
            }
        }

        return $query->get();
    }
}