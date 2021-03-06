<?php

namespace Exylon\Fuse\Console;

use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:repository';

    protected $type = 'Repository';

    protected $stub = __DIR__ . '/stubs/repository.stub';
    protected $namespace = '\Repositories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a repository class for the given model';

    public function handle()
    {
        $type = $this->option('type');
        $createInterface = !$this->option('no-interface');

        if (!in_array($type, [
            'eloquent'
        ])) {
            $this->error("Unsupported repository type '$type'");
            return false;
        }

        if ($createInterface) {
            $this->type = 'Repository';
            $this->stub = __DIR__ . '/stubs/repository.stub';
            $this->namespace = '\Repositories';
            parent::handle();

            $this->type = title_case($type) . " Repository";
            $this->stub = __DIR__ . "/stubs/repository-$type.stub";
            $this->namespace = '\Repositories\\' . title_case($type);
            parent::handle();
        } else {
            $this->type = title_case($type) . " Repository";
            $this->stub = __DIR__ . "/stubs/repository-$type.stub";
            $this->namespace = '\Repositories';
            parent::handle();
        }

        return true;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->stub;
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . $this->namespace;
    }

    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The name of the model'],
        ];
    }

    protected function getOptions()
    {
        return [
            ['type', null, InputOption::VALUE_OPTIONAL, 'Creates an Eloquent type repository', 'eloquent'],
            [
                'no-interface',
                null,
                InputOption::VALUE_NONE,
                'Create a concrete repository without the interface'
            ],
        ];
    }

    /**
     * @param $stub
     *
     * @return string
     */
    protected function replace(&$stub)
    {
        return \Exylon\Fuse\Support\Str::replaceAssoc([
            'DummyModelClass' => $this->getModelClass(),
            'DummyModel'      => $this->getModelName(),
        ], $stub);
    }

    protected function getModelName()
    {
        return trim(str_replace_last('Repository', '', $this->argument('model')));
    }

    protected function getModelClass()
    {
        $modelName = $this->getModelName();
        $modelClass = $modelName;
        if (!Str::startsWith($modelClass, [
            $this->laravel->getNamespace(),
            'Illuminate',
            '\\',
        ])) {
            $modelClass = $this->laravel->getNamespace() . 'Models\\' . $modelName;
        }
        if (class_exists($modelClass)) {
            return $modelClass;
        }
        $modelClass = $this->laravel->getNamespace() . $modelName;
        if (class_exists($modelClass)) {
            return $modelClass;
        }

        $this->warn("Model '$modelName' does not exists.");

        return $modelClass;
    }

    protected function getNameInput()
    {
        return $this->getModelName() . 'Repository';
    }
}
