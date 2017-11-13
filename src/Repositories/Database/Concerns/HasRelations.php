<?php


namespace Exylon\Fuse\Repositories\Database\Concerns;


use Illuminate\Container\Container;

trait HasRelations
{

    /**
     * @var array
     */
    protected $relationResolvers = [];

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * Load relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations)
    {
        $this->relations = array_unique(array_merge($this->relations,
            is_string($relations) ? func_get_args() : $relations));
        return $this;
    }

    /**
     * Registers a Relationship resolver
     *
     * @param string          $relation
     * @param string|callable $resolver
     */
    protected function registerRelationResolver(string $relation, $resolver)
    {
        $this->relationResolvers[$relation] = $resolver;
    }


    /**
     * @param \Illuminate\Support\Collection|array $result
     *
     * @return array|\Illuminate\Support\Collection
     */
    protected function applyRelations($result)
    {
        foreach ($this->relations as $relation) {
            if (array_key_exists($relation, $this->relationResolvers)) {
                $resolver = $this->relationResolvers[$relation];
                $result = Container::getInstance()->call($resolver, [$result,$relation]);
            }
        }
        return $result;
    }


    protected function resetRelations()
    {
        $this->relations = [];
    }
}
