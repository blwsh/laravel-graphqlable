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
    public static function graphQLQueryable(): array {
        return [];
    }

    /**
     * @return array An array of model methods that can be called by the GraphQL
     *               endpoint.
     */
    public static function graphQLMutatable(): array {
        return ['create', 'update', 'updateOrCreate'];
    }

    /**
     * @return string The name used for the generated GraphQL type.
     */
    public static function graphQLName(): string {
        return studly_case(class_basename(get_called_class()));
    }

    /**
     * @return string The description used for the generated GraphQL type.
     */
    public static function graphQLDescription(): string {
        return "Auto-generated GraphQL query type for " . get_called_class();
    }

    /**
     * @var GraphQLFieldMap A custom map the generateType method will use when
     *                      mapping fields to GraphQL types.
     */
    protected static $graphQLFieldMap;
    /**
     * @var ObjectType Stores ObjectType singleton.
     */
    private static $generatedType;

    /**
     * @return array Get the queryable attributes for the model. If the queryable
     *               array is empty then the fillable attributes array will be
     *               returned instead. If fillable is also empty all fields
     *               excluding guarded fields will be returned or nothing if all
     *               guarded.
     *
     * @todo Add relationship support
     */
    protected static function getQueryable(): array {
        $model = new static;

        /** @var Model|self self */
        if ($model::graphQLQueryable()) {
            return $model::graphQLQueryable();
        }
        else if ($model->getFillable()) {
            return $model->getFillable();
        }

        $relations = $model->getRelations();

        $fields = self::getModelDbFields();

        if ($model->getGuarded() != [0 => '*']) {
            $fields->filter(function($field) {
                return in_array($field->Name, self::getGuarded());
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
    public static function getMappedGraphQLFields(): array {
        $model = new static;

        $result = [];

        $fields = self::getModelDbFields();
        $queryable = self::getQueryable();

        $fields
            ->map(function($field) use($model, $queryable, &$result) {
                if (in_array($field->Field, $queryable)) {
                    $result[$field->Field] = GraphQLFieldMapper::map($field, $model, $model::$graphQLFieldMap);
                }
            });

        return $result;
    }

    /**
     * @return array An array of all mutatable fields that can be called by the
     *               GraphQL endpoint.
     */
    public static function getMutatables(): array  {
        return self::graphQLMutatable();
    }

    /**
     * Generates an ObjectType for the model using getMappedGraphQLFields
     * method.
     *
     * @return ObjectType The GraphQL type
     */
    public static function generateType(): ObjectType {
        if (self::$generatedType) return self::$generatedType;

        return self::$generatedType = new ObjectType([
            'name' =>  self::graphQLName(),
            'description' => self::graphQLDescription(),
            'fields' => self::getMappedGraphQLFields()
        ]);
    }

    /**
     * Unions should be used for polymorphic types.
     *
     * @todo Implement this
     */
    public static function generateUnionObject(): UnionType {
        return new UnionType([]);
    }

    /**
     * @return Collection A Collection of fields found in the database for the
     *                    model.
     */
    private static function getModelDbFields(): Collection {
        /** @var Model|self self */
        $model = new static;
        return $model->newQuery()->fromQuery("SHOW FIELDS FROM " . $model->getTable());
    }
}