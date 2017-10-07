<?php

namespace Exylon\Fuse\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class SubscriberMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:subscriber';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new events subscriber class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Subscriber';


    protected $methodSnippet = 'public function onDummyEvent(DummyEvent $event)
    {
        //
    }
    
    //METHOD_BLOCK';

    protected $listenSnippet = '$events->listen(\'DummyFullEvent\', \'DummyNamespace\DummyClass@onDummyEvent\');
        //LISTEN_BLOCK';

    /**
     * Build the class with the given name.
     *
     * @param  string $name
     *
     * @return string
     */
    protected function buildClass($name)
    {
        $events = $this->option('event');
        $stub = parent::buildClass($name);
        foreach ($events as $event) {
            $event = $this->resolveEvent($this->option('event'));

            $stub = str_replace_assoc([
                '//METHOD_BLOCK' => $this->methodSnippet,
                '//LISTEN_BLOCK' => $this->listenSnippet
            ], $stub);
            $stub = str_replace(
                'DummyEvent', class_basename($event), $stub
            );

            return str_replace(
                'DummyFullEvent', $event, $stub
            );
        }

        $stub = str_replace_assoc([
            '//METHOD_BLOCK' => '',
            '//LISTEN_BLOCK' => ''
        ], $stub);
        return $stub;

    }

    protected function resolveEvent($event)
    {
        if (!Str::startsWith($event, [
            $this->laravel->getNamespace(),
            'Illuminate',
            '\\',
        ])) {
            $event = $this->laravel->getNamespace() . 'Events\\' . $event;
        }
        return $event;
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        return $this->option('event')
            ? __DIR__ . '/stubs/subscriber.stub'
            : __DIR__ . '/stubs/subscriber-duck.stub';
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string $rawName
     *
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return class_exists($rawName);
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
        return $rootNamespace . '\Listeners';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['event', 'e', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'The event class/es being listened for.'],
        ];
    }
}
