<?php

namespace UniBen\LaravelGraphQLable\Traits;

use Illuminate\Support\Collection;
use GraphQL\Type\Definition\UnionType;
use GraphQL\Type\Definition\ObjectType;
use UniBen\LaravelGraphQLable\utils\GraphQLFieldMapper;
use UniBen\LaravelGraphQLable\structures\GraphQLFieldMap;

/**
 * Class GraphQLMutatableTrait
 *
 * This class can be extended to allow dynamic generation of GraphQL queries and
 * mutations
 *
 * @package UniBen\LaravelGraphQLable
 */
trait GraphQLMutatableTrait
{
    /**
     * @return array
     */
    public function graphQLQueryable(): array {
        return [];
    }

    /**
     * @return string
     */
    public function graphQLName(): string {
        return studly_case(class_basename($this));
    }

    /**@return string
     */
    public function graphQLDescription(): string {
        return "Auto-generated GraphQL query type for " . get_class($this);
    }

    /**
     * @var GraphQLFieldMap
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
    public function getQueryable(): array {
        if ($this->graphQLQueryable()) {
            return $this->graphQLQueryable();
        }
        else if ($this->getFillable()) {
            return $this->getFillable();
        }

        $relations = $this->getRelations();

        $fields = $this->getFields();

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
     * @return array an array of all queryable fields mapped to
     *               GraphQL\Type\Definition\Type via GraphQLFieldMapper. If the
     *               graphQLFieldMap has a GraphQLFieldMap set it will attempt
     *               to map fields based on that map first and fallback to the
     *               config map if no field map is found.
     */
    public function mapFieldsToType(): array {
        $result = [];

        $fields = $this->getFields();
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
     * @return ObjectType
     */
    public function generateQueryObject(): ObjectType {
        return new ObjectType([
            'name' =>  $this->graphQLName(),
            'description' => $this->graphQLDescription(),
            'fields' => $this->mapFieldsToType()
        ]);
    }

    /**
     * Unions should be used for polymorphic types.
     *
     * @todo Implement this
     */
    public function generateUnionObject(): UnionType {

    }

    /**
     * @return Collection
     */
    private function getFields(): Collection {
        return $this->newQuery()->fromQuery("SHOW FIELDS FROM " . $this->getTable());
    }
}