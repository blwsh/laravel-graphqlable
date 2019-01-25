<?php

namespace UniBen\LaravelGraphQLable\Traits;

use Illuminate\Support\Collection;
use GraphQL\Type\Definition\UnionType;
use Illuminate\Database\Eloquent\Model;
use GraphQL\Type\Definition\ObjectType;
use UniBen\LaravelGraphQLable\utils\GraphQLFieldMapper;
use UniBen\LaravelGraphQLable\structures\GraphQLFieldMap;

/**
 * Class GraphQLableTrait
 *
 * This class can be extended to allow dynamic generation of GraphQL queries and
 * mutations
 *
 * @package UniBen\LaravelGraphQLable
 */
trait GraphQLQueryableTrait
{
    /**
     * @return array An array of model fields that can be queried by the GraphQL
     *               endpoint.
     */
    public function graphQLQueryable(): array {
        return [];
    }

    /**
     * @return array An array of model methods that can be called by the GraphQL
     *               endpoint.
     */
    public function graphQLMutatable(): array {
        return ['create', 'update', 'updateOrCreate'];
    }

    /**
     * @return string The name used for the generated GraphQL type.
     */
    public function graphQLName(): string {
        return studly_case(class_basename($this));
    }

    /**
     * @return string The description used for the generated GraphQL type.
     */
    public function graphQLDescription(): string {
        return "Auto-generated GraphQL query type for " . get_class($this);
    }

    /**
     * @var GraphQLFieldMap A custom map the generateType method will use when
     *                      mapping fields to GraphQL types.
     */
    protected $graphQLFieldMap;

    /**
     * @return array Get the queryable attributes for the model. If the queryable
     *               array is empty then the fillable attributes array will be
     *               returned instead. If fillable is also empty all fields
     *               excluding guarded fields will be returned or nothing if all
     *               guarded.
     *
     * @todo Add relationship support
     */
    protected function getQueryable(): array {
        /** @var Model|$this $this */
        if ($this->graphQLQueryable()) {
            return $this->graphQLQueryable();
        }
        else if ($this->getFillable()) {
            return $this->getFillable();
        }

        $relations = $this->getRelations();

        $fields = $this->getModelDbFields();

        if ($this->getGuarded() != [0 => '*']) {
            $fields->filter(function($field) {
                return in_array($field->Name, $this->getGuarded());
            });
        }

        return $fields->map(function($field) {
                return $field['Field'];
            })
            ->toArray();
    }

    /**
     * @return array An array of all queryable fields mapped to
     *               GraphQL\Type\Definition\Type via GraphQLFieldMapper. If the
     *               graphQLFieldMap has a GraphQLFieldMap set it will attempt
     *               to map fields based on that map first and fallback to the
     *               config map if no field map is found.
     */
    public function getMappedGraphQLFields(): array {
        $result = [];

        $fields = $this->getModelDbFields();
        $queryable = $this->getQueryable();

        $fields
            ->map(function($field) use($queryable, &$result) {
                if (in_array($field->Field, $queryable)) {
                    $result[$field->Field] = GraphQLFieldMapper::map($field, $this, $this->graphQLFieldMap);
                }
            });

        return $result;
    }

    /**
     * @return array An array of all mutatable fields that can be called by the
     *               GraphQL endpoint.
     */
    public function getMutatables(): array  {
        return $this->graphQLMutatable();
    }

    /**
     * Generates an ObjectType for the model using getMappedGraphQLFields
     * method.
     *
     * @return ObjectType The GraphQL type
     */
    public function generateType(): ObjectType {
        return new ObjectType([
            'name' =>  $this->graphQLName(),
            'description' => $this->graphQLDescription(),
            'fields' => $this->getMappedGraphQLFields()
        ]);
    }

    /**
     * Unions should be used for polymorphic types.
     *
     * @todo Implement this
     */
    public function generateUnionObject(): UnionType {
        return new UnionType([]);
    }

    /**
     * @return Collection A Collection of fields found in the database for the
     *                    model.
     */
    private function getModelDbFields(): Collection {
        /** @var Model|$this $this */
        return $this->newQuery()->fromQuery("SHOW FIELDS FROM " . $this->getTable());
    }
}