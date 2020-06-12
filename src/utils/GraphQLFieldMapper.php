<?php

namespace UniBen\LaravelGraphQLable\utils;

use Exception;
use GraphQL\Type\Definition\Type;
use UniBen\LaravelGraphQLable\structures\GraphQLFieldMap;
use UniBen\LaravelGraphQLable\traits\GraphQLQueryableTrait;

class GraphQLFieldMapper
{
    /**
     * @var array Stores the types
     */
    protected static $types = [];

    /**
     * @param                      $field
     * @param GraphQLQueryableTrait $model
     * @param GraphQLFieldMap|null $overrideMap
     *
     * @return array
     * @throws Exception
     */
    public static function map($field, $model, GraphQLFieldMap $overrideMap = null)
    {
        if ($overrideMap) {
            // @todo Add override map support.
            throw new Exception('Sorry, overriding via the model isn\'t yet supported. Add overrides using config/graphqlable.php');
        }

        // Preg match the first word of the fields type.
        preg_match('/\w+/', $field->Type, $fieldType); $fieldType = $fieldType[0];

        if ($field->Key && $fieldType == 'int') {
            // The filed is a primary/foreign key and an int therefore, has the type of id.
            return [
                'type' => Type::id()
            ];
        } else {
            // We find the type based on config, config default value and static
            // default value.
            /*$type = config(
                "laravelgraphqlable.$fieldType",
                config(
                    "laravelgraphqlable.default",
                    StringType::class
                )
            );*/

            // If the type is not yet defined we set store it in the static array
            // to prevent multiple definitions of the same type.
            /*if (!isset(self::$types[$type])) self::$types[$type] = new $type;*/

            // Instead we'll just try to use the graphql-php service

            return [
                'type' => in_array($fieldType, ['int', 'string', 'float', 'boolean']) ? Type::{$fieldType}() : $fieldType == 'tinyint' ? Type::boolean() : Type::string()
            ];
        }
    }
}
