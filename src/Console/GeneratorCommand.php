<?php


namespace Exylon\Fuse\Console;


abstract class GeneratorCommand extends \Illuminate\Console\GeneratorCommand
{


    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        return $this->replace($stub);
    }

    /**
     * Determine if the class already exists.
     *
     * @param  string  $rawName
     * @return bool
     */
    protected function alreadyExists($rawName)
    {
        return class_exists($rawName);
    }

    /**
     * @param $stub
     *
     * @return string
     */
    protected abstract function replace(&$stub);
}
