<?php


namespace Exylon\Fuse\Contracts;


interface Sortable
{

    const ASCENDING  = 'asc';
    const DESCENDING  = 'desc';
    const RANDOM  = 'random';
    /**
     * Orders the entity collection by the given field
     *
     * @param string       $field
     * @param string $method
     *
     * @return mixed
     */
    public function orderBy($field, $method = 'asc');
}
