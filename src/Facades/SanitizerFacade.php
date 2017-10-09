<?php


class SanitizerFacade extends \Illuminate\Support\Facades\Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fuse.sanitizer';
    }

}
