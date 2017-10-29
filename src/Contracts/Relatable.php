<?php


namespace Exylon\Fuse\Contracts;


interface Relatable
{
    /**
     * Load relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations);
}
