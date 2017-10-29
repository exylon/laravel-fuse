<?php


namespace Exylon\Fuse\Contracts;


interface Transformable
{
    /**
     * Enables the transformer. If none is provided, the default transformer will be used
     *
     * @param \Exylon\Fuse\Contracts\Transformer|\Closure|mixed $transformer
     *
     * @return $this
     */
    public function withTransformer($transformer);

    /**
     * Disables any transformer including the default
     *
     * @return $this
     */
    public function withoutTransformer();

    /**
     * Sets the default transformer
     *
     * @param \Exylon\Fuse\Contracts\Transformer|\Closure|mixed|null $transformer
     */
    public function setDefaultTransformer($transformer);
}
