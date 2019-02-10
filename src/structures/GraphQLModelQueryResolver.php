<?php

namespace UniBen\LaravelGraphQLable\Structures;

class GraphQLModelQueryResolver extends GraphQLResolver
{
    /**
     * @return mixed This method should return data that can be resolved to the
     *               type of the graphQL resource requested.
     */
    public function resolve()
    {
        $query = $this->model::query();

        $query->addSelect('id');

        foreach ($this->info->getFieldSelection(2) as $field => $value) {
            if (is_array($value)) {
                $query->with([$field => function($query) use ($value) {
                    $query->addSelect(array_keys($value));
                }]);
            } else {
                $query->addSelect($field);
            }
        }

        return $query->get()->toArray();
    }

}