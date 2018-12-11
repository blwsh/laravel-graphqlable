<?php

namespace UniBen\LaravelGraphQLable\Models;

use GraphQL\Type\Definition\ObjectType;
use Illuminate\Database\Eloquent\Model;
use UniBen\LaravelGraphQLable\Utils\GraphQLFieldMapper;

/**
 * Class GraphQLModel
 *
 * This class can be extended to allow dynamic generation of GraphQL queries and
 * mutations
 *
 * @package App
 */
class GraphQLModel extends Model
{
    /**
     * @var array A list of all queryable fields for this model. If empty all
     *            excluding the guarded fields will be queryable.
     */
    protected $queryable = [];

    /**
     * @var string The name of the GraphQLType
     */
    protected $name;

    /**
     * @var string A description fo the GraphQLType
     */
    protected $description;

    /**
     * @var GraphQLFieldMap
     */
    protected $graphQLFieldMap;

    /**
     * GraphQLQueryable constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

    /**
     * @return array Get the queryable attributes for the model. If the queryable
     *               array is empty then the fillable attributes array will be
     *               returned instead. If fillable is also empty all fields
     *               excluding guarded fields will be returned or nothing if all
     *               guarded.
     *
     * @todo Add relationship support
     */
    public function getQueryable() {
        if ($this->queryable) {
            return $this->queryable;
        }
        else if ($this->getFillable()) {
            return $this->getFillable();
        }

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
    public function mapFieldsToType() {
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
    public function generateQueryObject() {
        return new ObjectType([
            'name' => studly_case($this->name ? $this->name : class_basename($this)),
            'description' => $this->description ? $this->description : "Auto-generated GraphQL query type for " . get_class($this),
            'fields' => $this->mapFieldsToType()
        ]);
    }

    /**
     * Unions should be used for polymorphic types.
     */
    public function generateUnionObject() {

    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getFields() {
        return $this->newQuery()->fromQuery("SHOW FIELDS FROM " . $this->getTable());
    }
}