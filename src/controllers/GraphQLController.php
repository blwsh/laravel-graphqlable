<?php

namespace UniBen\LaravelGraphQLable\controllers;

use Exception;
use GraphQL\Type\Schema;
use GraphQL\Error\FormattedError;
use GraphQL\Type\Definition\Type;
use GraphQL\Server\StandardServer;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use UniBen\LaravelGraphQLable\Traits\GraphQLQueryableTrait;

class GraphQLController extends Controller
{
    public function view() {
        // Register models and mutations
        $models = $mutations = [];

        foreach ($this->getGraphQLModels() as $model) {
            try {
                /**
                 * @var Model|GraphQLQueryableTrait $initModel
                 */
                $initModel = new $model->classname();
                $graphQLType = $initModel->generateType();

                // Add the generated model type
                $models[$graphQLType->name] = [
                    'name' => $graphQLType->name,
                    'type' => Type::listOf($graphQLType),
                    'resolve' => function($value, $args, $context, ResolveInfo $info) use ($initModel) {
                        return $initModel->newQuery()->select(array_keys($info->getFieldSelection()))->get()->toArray();
                    }
                ];

                foreach ($initModel->getMutatables() as $operation) {
                    $mutations[camel_case("$operation $graphQLType->name")] = [
                        'args' => $initModel->getMappedGraphQLFields(),
                        'type' => $graphQLType,
                        'resolve' => function($rootValue, ...$args) use ($initModel, $operation) {
                            return $initModel->$operation(...$args);
                        }
                    ];
                }
            } catch (Exception $e) {
                $models['errors'] = [
                    'name' => explode('\\', $model->classname)[0],
                    'type' => Type::string(),
                    'resolve' => function() use ($e) {
                        return $e->getMessage();
                    }
                ];
            }
        }

        $schema = new Schema([
            'query' => ($models ? new ObjectType([
                'name' => 'query',
                'fields' => $models
            ]) : null),
            'mutation' => ($mutations ? new ObjectType([
                'name' => 'mutation',
                'fields' => $mutations
            ]) : null)
        ]);

        try {
            $schema->assertValid();
        } catch (Exception $e) {
            return ['errors' => FormattedError::createFromException($e, true)];
        }

        // Start the server
        try {
            $server = new StandardServer([
                'schema' => $schema,
                'debug' => config('app.debug')
            ]);

            $server->handleRequest(null, true);
        } catch (\Exception $e) {
            return ['errors' => FormattedError::createFromException($e, true)];
        }
    }

    function getGraphQLModels()
    {
        $classes = File::allFiles(app_path());
        foreach ($classes as $class) {
            $class->classname = str_replace(
                [app_path(), '/', '.php'],
                ['App', '\\', ''],
                $class->getRealPath()
            );
        }

        $classes = collect($classes)
            ->filter(function($model) {
                return in_array(GraphQLQueryableTrait::class, class_uses($model->classname));
            })
            ->toArray();

        return $classes;
    }
}
