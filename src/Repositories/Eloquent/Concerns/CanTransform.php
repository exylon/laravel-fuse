<?php


namespace Exylon\Fuse\Repositories\Eloquent\Concerns;


use Exylon\Fuse\Repositories\Entity;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
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
     * Transform an Eloquent model into a more abstract object. By default, using Entity object
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array                               $metadata
     *
     * @return \Exylon\Fuse\Repositories\Entity|mixed
     */
    protected function transform(Model $model, array $metadata = [])
    {
        if (!empty($this->appends)) {
            $model = $model->append($this->appends);
        }

        if ($this->enableTransformer) {
            if (($callback = $this->getTransformerCallback($this->transformer, $this->defaultTransformer)) !== null) {
                $result = $this->executeTransformer($callback, $model, $metadata);
            } else {
                $result = $this->prepareEntity($model, $metadata);
            }
        } else {
            $result = $this->prepareEntity($model, $metadata);
        }

        $this->resetTransformer();

        return $result;
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
     * Transform a Model or collection of Models into Entity objects
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array                               $metadata
     *
     * @return \Exylon\Fuse\Repositories\Entity
     */
    private function prepareEntity(Model $model, array $metadata = [])
    {
        $attributes = $model->attributesToArray();
        if (!empty($metadata)) {
            $attributes = array_merge($attributes, [
                'meta' => $metadata
            ]);
        }
        $root = new Entity($model->getKey(), $attributes);
        foreach ($model->getRelations() as $name => $relation) {
            if ($relation instanceof Collection) {
                $root[$name] = $relation->map(function ($item) {
                    return $this->prepareEntity($item);
                });
            } elseif ($relation instanceof Model) {
                $root[$name] = $this->prepareEntity($relation);
            }
        }
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
                [Container::getInstance()->make($segments[0]), $method],
                $model,
                $metadata);
        }

        throw new InvalidArgumentException('Invalid transformer callback');
    }
}
