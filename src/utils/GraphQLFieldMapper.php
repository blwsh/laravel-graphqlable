<?php
/**
 * Created by PhpStorm.
 * User: work
 * Date: 30/11/2018
 * Time: 14:25
 */

namespace UniBen\LaravelGraphQLable\utils;

use function config;
use Exception;
use GraphQL\Type\Definition\Type;
use UniBen\LaravelGraphQLable\structures\GraphQLFieldMap;
use UniBen\LaravelGraphQLable\traits\GraphQLQueryableTrait;

class GraphQLFieldMapper
{
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
            // @todo
            throw new Exception('Sorry, overriding via the model isn\'t yet supported. Add overrides using config/graohql.php');

            // if ($type = $overrideMap->getField($field)) {
            //     return $type;
            // }
        }

        // Find the type in config
        return [
            'type' => config("graphql.$field->Type", config('graphql.default', Type::string()))
        ];
    }
}