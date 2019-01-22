<?php

namespace UniBen\LaravelGraphQLable\controllers;

use function class_uses;
use Exception;
use function explode;
use GraphQL\Error\FormattedError;
use GraphQL\Type\Schema;
use GraphQL\Type\Definition\Type;
use GraphQL\Server\StandardServer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use function now;
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
                $models['errors'] = [
                    'name' => explode('\\', $model->classname)[0],
                    'type' => Type::string(),
                    'resolve' => function() use ($e) {
                        return [$e->getMessage()];
                    }
                ];
            }
        }

        $queryType = new ObjectType([
            'name' => 'query',
            'fields' => $models
        ]);

        $schema = new Schema([
            'query' => $queryType
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
                return class_uses($model->classname);
            })
            ->toArray();

        return $classes;
    }
}
