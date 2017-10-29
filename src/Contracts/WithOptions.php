<?php


namespace Exylon\Fuse\Contracts;


interface WithOptions
{
    /**
     * Overrides the default options for this repository
     *
     * @param array $options
     */
    public function setDefaultOptions(array $options);

    /**
     * Use options
     *
     * @param array $options
     *
     * @return $this
     */
    public function withOptions(array $options);
}
