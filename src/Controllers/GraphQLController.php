<?php

namespace UniBen\LaravelGraphQLable\Controllers;

use Exception;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Server\StandardServer;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use UniBen\LaravelGraphQLable\Models\GraphQLModel;

class GraphQLController extends Controller
{
    public function view() {
        // Register models
        $models = [];
        foreach ($this->getGraphQLModels() as $model) {
            try {
                /**
                 * @var GraphQLModel $initModel
                 */
                $initModel = new $model->classname();
                $graphQLModel = $initModel->generateQueryObject();

                // Add the generated model type
                $models[$graphQLModel->name] = [
                    'name' => $graphQLModel->name,
                    'type' => Type::listOf($graphQLModel),
                    'resolve' => function($value, $args, $context, ResolveInfo $info) use ($initModel) {
                        return $initModel->newQuery()->select(array_keys($info->getFieldSelection()))->get()->toArray();
                    }
                ];
            } catch (Exception $e) {
                Log::error('Unable to add GraphQLModel ' . $model);
            }
        }

        $queryType = new ObjectType([
            'name' => 'query',
            'fields' => $models
        ]);

        $schema = new Schema([
            'query' => $queryType
        ]);

        $schema->assertValid();

        // Start the server
        try {
            $server = new StandardServer([
                'schema' => $schema,
                'debug' => config('app.debug')
            ]);

            $server->handleRequest(null, true);
        } catch (\Exception $e) {
            StandardServer::send500Error($e);
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
                return is_subclass_of($model->classname, GraphQLModel::class);
            })
            ->toArray();

        return $classes;
    }
}
