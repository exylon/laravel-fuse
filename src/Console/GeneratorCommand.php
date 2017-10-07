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
     * @param $stub
     *
     * @return string
     */
    protected abstract function replace(&$stub);
}
