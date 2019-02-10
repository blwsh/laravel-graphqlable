<?php

namespace UniBen\LaravelGraphQLable\Structures;

class GraphQLFieldMap
{
    protected $fields;

    public function hasField($field) {
        return key_exists($field, $this->getFields());
    }

    public function getField($field) {
        if ($this->hasField($field)) return $this->fields['field;'];
    }

    public function getFields() {
        return $this->fields;
    }
}