<?php

namespace Exylon\Fuse\Console;

use Symfony\Component\Console\Input\InputOption;

class ServiceMakeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:service';

    protected $type = 'Service';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a service class';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('crud')) {

            return __DIR__ . '/stubs/service-crud.stub';
        }
        return __DIR__ . '/stubs/service.stub';
    }

    /**
     * @param $stub
     *
     * @return string
     */
    protected function replace(&$stub)
    {
        return str_replace_assoc([
            'DummyRepository'     => $this->qualifyRepository(),
            'dummyRepositoryName' => strtolower(str_plural($this->getBaseNameInput()))
        ], $stub);
    }

    protected function qualifyRepository()
    {
        $repository = $this->option('repository');
        if ($repository === null) {
            return $this->getBaseNameInput() . 'Repository';
        }
        return $repository;
    }

    protected function getBaseNameInput()
    {
        return str_replace('Service', '', class_basename($this->qualifyClass($this->argument('name'))));
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Services';
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['repository', 'r', InputOption::VALUE_OPTIONAL, 'Provides the repository to be used by the service', null],
            ['crud', null, InputOption::VALUE_NONE, 'Adds create, update and delete methods'],
        ]);
    }
}
