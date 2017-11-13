<?php


namespace Exylon\Fuse\Repositories\Database\Concerns;


use Illuminate\Container\Container;

trait HasAppendableAttributes
{

    /**
     * @var array
     */
    protected $appendResolvers = [];

    /**
     * @var array
     */
    protected $appends = [];

    /**
     * @var array
     */
    protected $defaultAppends = [];

    /**
     * Append attributes
     *
     * @param array|string $attributes
     *
     * @return $this
     */
    public function append($attributes)
    {
        $this->appends = array_unique(
            array_merge($this->appends, is_string($attributes) ? func_get_args() : $attributes)
        );
        return $this;
    }

    /**
     * Registers a Append-able attribute resolver
     *
     * @param string          $attribute
     * @param string|callable $resolver
     */
    protected function registerAppendResolver(string $attribute, $resolver)
    {
        $this->appendResolvers[$attribute] = $resolver;
    }

    protected function applyAppends(array &$attributes)
    {
        $appends = array_unique(array_merge($this->defaultAppends, $this->appends));
        foreach ($appends as $attribute) {
            if (array_key_exists($attribute, $this->appendResolvers)) {
                $resolver = $this->appendResolvers[$attribute];
                $attributes[$attribute] = Container::getInstance()->call($resolver, [$attributes]);
            }
        }
    }

    protected function resetAppends()
    {
        $this->appends = [];
    }
}
