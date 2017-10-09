<?php

namespace Exylon\Fuse\Facades;

use Illuminate\Support\Facades\Facade;

class Sanitizer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fuse.sanitizer';
    }

}
