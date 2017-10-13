<?php

namespace Tests;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return [
            \Orchestra\Database\ConsoleServiceProvider::class,
            \Exylon\Fuse\FuseServiceProvider::class
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'FuseSanitizer' => \Exylon\Fuse\Facades\Sanitizer::class
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $config = require __DIR__ . '/../resources/config/fuse.php';
        $app['config']->set('fuse', $config);
    }
}
