<?php


namespace Exylon\Fuse\Repositories;


use Exylon\Fuse\Support\Attributes;

class Entity extends Attributes
{

    /**
     * @var mixed
     */
    protected $key;

    public function __construct($primaryKey, array $attributes, array $aliases = [])
    {
        parent::__construct($attributes, $aliases);

        $this->key = $primaryKey;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }


}
