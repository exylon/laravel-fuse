<?php

namespace Exylon\Fuse\Console;

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
    protected $description = 'Creates a repository for the given model';

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
            ['type', '', InputOption::VALUE_NONE, 'Creates an Eloquent type repository', 'eloquent'],
            [
                'no-interface',
                '',
                InputOption::VALUE_OPTIONAL,
                'Create a concrete repository without the interface',
                false
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
        return str_replace_assoc([
            'DummyModel' => $this->getNameInput()
        ], $stub);
    }

    protected function getNameInput()
    {
        if (ends_with($this->argument('model'), 'Repository')) {
            return trim(str_replace_last('Repository', '', $this->argument('model')));
        }
        return trim($this->argument('model') . 'Repository');
    }
}
