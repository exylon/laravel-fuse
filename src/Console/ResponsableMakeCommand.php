<?php


namespace Exylon\Fuse\Console;


use Symfony\Component\Console\Input\InputOption;

class ResponsableMakeCommand extends GeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:response';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new response class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Response';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('entity')) {
            return __DIR__ . '/stubs/responsable-entity.stub';
        }
        return __DIR__ . '/stubs/responsable.stub';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     *
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Responses';
    }

    /**
     * @param $stub
     *
     * @return string
     */
    protected function replace(&$stub)
    {
        return $stub;
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'entity',
                null,
                InputOption::VALUE_NONE,
                'Sets the response class as entity response'
            ]
        ]);
    }
}
