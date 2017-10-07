<?php


namespace Exylon\Fuse\Contracts;


interface Transformer
{

    /**
     * Transformer handler
     *
     * @param $entity
     *
     * @return mixed
     */
    public function transform($entity);
}
