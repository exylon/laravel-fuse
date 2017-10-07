<?php

namespace Exylon\Fuse\Console;

use Symfony\Component\Console\Input\InputArgument;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:repository';

    protected $type = 'Repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a repository for the given model';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return __DIR__ . '/stubs/repository.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Repositories';
    }

    protected function getNameInput()
    {
        if (ends_with($this->argument('model'), 'Repository')) {
            return trim(str_replace_last('Repository', '', $this->argument('model')));
        }
        return trim($this->argument('model') . 'Repository');
    }


    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'The name of the model'],
        ];
    }


    /**
     * @param $stub
     * @return string
     */
    protected function replace(&$stub)
    {
        return str_replace_assoc([
            'DummyModel' => $this->getNameInput()
        ], $stub);
    }
}
