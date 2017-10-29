<?php


namespace Exylon\Fuse\Contracts;


interface Appendable
{
    /**
     * Append attributes
     *
     * @param array|string $attributes
     *
     * @return $this
     */
    public function append($attributes);

}
