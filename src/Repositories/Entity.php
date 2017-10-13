<?php


namespace Exylon\Fuse\Repositories;


use Exylon\Fuse\Support\Attributes;

class Entity extends Attributes
{

    /**
     * @var mixed
     */
    protected $key;

    public function __construct(array $attributes, $primaryKey)
    {
        parent::__construct($attributes);

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
