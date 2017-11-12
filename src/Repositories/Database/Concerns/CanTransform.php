<?php


namespace Exylon\Fuse\Repositories\Database\Concerns;


use Exylon\Fuse\Repositories\Entity;
use Exylon\Fuse\Support\Arr;
use InvalidArgumentException;

trait CanTransform
{
    /**
     * @var bool
     */
    protected $enableTransformer = true;

    /**
     * @var \Exylon\Fuse\Contracts\Transformer
     */
    protected $transformer = null;

    /**
     * @var \Exylon\Fuse\Contracts\Transformer
     */
    protected $defaultTransformer = null;

    /**
     * Enables the transformer. If none is provided, the default transformer will be used
     *
     * @param \Exylon\Fuse\Contracts\Transformer|\Closure|mixed $transformer
     *
     * @return $this
     */
    public function withTransformer($transformer)
    {
        if ($transformer === null) {
            $transformer = $this->defaultTransformer;
        }
        $this->transformer = $transformer;
        $this->enableTransformer = true;
        return $this;
    }


    /**
     * Disables any transformer including th default
     *
     * @return $this
     */
    public function withoutTransformer()
    {
        $this->enableTransformer = false;
    }

    /**
     * Sets the default transformer
     *
     * @param \Exylon\Fuse\Contracts\Transformer|\Closure|mixed|null $transformer
     */
    public function setDefaultTransformer($transformer)
    {
        $this->defaultTransformer = $transformer;
    }

    /**
     * Resets the transformer
     */
    protected function resetTransformer()
    {
        $this->transformer = null;
        $this->enableTransformer = true;
    }


    /**
     * Transforms the object
     *
     * @param array $attributes
     * @param array $metadata
     *
     * @return \Exylon\Fuse\Repositories\Entity|mixed
     */
    protected function transform(array $attributes, array $metadata = [])
    {
        if ($this->enableTransformer) {
            if (($callback = $this->getTransformerCallback($this->transformer, $this->defaultTransformer)) !== null) {
                $result = $this->executeTransformer($callback, $attributes, $metadata);
            } else {
                $result = $this->prepareEntity($attributes, $metadata);
            }
        } else {
            $result = $this->prepareEntity($attributes, $metadata);
        }

        $this->resetTransformer();

        return $result;
    }

    /**
     * Transform a Model or collection of Models into Entity objects
     *
     * @param array $attributes
     * @param array $metadata
     *
     * @return \Exylon\Fuse\Repositories\Entity
     * @internal param array $model
     */
    private function prepareEntity(array $attributes, array $metadata = [])
    {
        if (!empty($metadata)) {
            $attributes = array_merge($attributes, [
                'meta' => $metadata
            ]);
        }
        $primaryKey = Arr::get($attributes, $this->primaryKeyName, null);
        foreach ($attributes as $key => $attribute) {
            if (is_array($attribute) && Arr::isAssoc($attribute)) {
                $attributes[$key] = $this->prepareEntity($attribute);
            }
        }
        $root = new Entity($primaryKey, $attributes);
        return $root;
    }

    /**
     * Get which transformer to use
     *
     * @param      $transformer
     * @param null $defaultTransformer
     *
     * @return array|null
     */
    private function getTransformerCallback($transformer, $defaultTransformer = null)
    {
        if (is_callable($transformer) || is_string($transformer) || is_array($transformer)) {
            return $transformer;
        } elseif (is_object($transformer)) {
            return [$transformer, 'transform'];
        }
        return $defaultTransformer === null ? null : $this->getTransformerCallback($defaultTransformer);
    }

    /**
     * @param       $callback
     * @param       $model
     * @param array $metadata
     *
     * @return mixed
     */
    private function executeTransformer($callback, $model, array $metadata = [])
    {
        if (is_callable($callback)) {
            return call_user_func($callback, $model, $metadata);
        }

        if (is_string($callback) && strpos($callback, '@') !== false) {
            $segments = explode('@', $callback);
            $method = count($segments) == 2
                ? $segments[1] : 'transform';
            return $this->executeTransformer(
                [app()->make($segments[0]), $method],
                $model,
                $metadata);
        }

        throw new InvalidArgumentException('Invalid transformer callback');
    }
}
