<?php
/**
 * Created by PhpStorm.
 * User: work
 * Date: 30/11/2018
 * Time: 14:25
 */

namespace UniBen\LaravelGraphQLable\Utils;

use Exception;
use GraphQL\Type\Definition\Type;
use UniBen\LaravelGraphQLable\Models\GraphQLModel;
use UniBen\LaravelGraphQLable\Structures\GraphQLFieldMap;

class GraphQLFieldMapper
{
    /**
     * @param                      $field
     * @param GraphQLModel         $model
     * @param GraphQLFieldMap|null $overrideMap
     *
     * @return array
     * @throws Exception
     */
    public static function map($field, GraphQLModel $model, GraphQLFieldMap $overrideMap = null)
    {
        if ($overrideMap) {
            // @todo
            throw new Exception('Sorry, overriding via the model isn\'t yet supported. Add overrides using config/graohql.php');

            // if ($type = $overrideMap->getField($field)) {
            //     return $type;
            // }
        }

        return [
            'type' => config("graphql.$field->Type", Type::string())
        ];
    }
}