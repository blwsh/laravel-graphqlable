<?php namespace UniBen\LaravelGraphQLable\utils;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use UniBen\LaravelGraphQLable\Traits\GraphQLQueryableTrait;

/**
 * Class DiscoverGraphQLModels
 * @package UniBen\LaravelGraphQLable\utils
 */
class DiscoverGraphQLModels
{
    /**
     * @var array
     */
    protected $classes;

    /**
     * DiscoverGraphQLModels constructor.
     */
    public function __construct()
    {
        $classes = File::allFiles(app_path());

        foreach ($classes as $class) {
            $class->classname = str_replace(
                [app_path(), '/', '.php'],
                ['App', '\\', ''],
                $class->getRealPath()
            );
        }

        $this->classes = collect($classes)
            ->filter(function(SplFileInfo $model) {
                if ($model->getExtension() != 'php' || !$model->isFile()) return false;

                if (class_exists($model->classname)) {
                    return in_array(GraphQLQueryableTrait::class, class_uses($model->classname));
                }
            })
            ->toArray();
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->classes;
    }
}
