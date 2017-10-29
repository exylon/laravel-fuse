<?php


namespace Exylon\Fuse\Repositories\Eloquent\Concerns;


trait HasRelations
{

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
     * Applies all added relations to the query
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Model $query
     *
     * @return $this|\Illuminate\Database\Eloquent\Builder
     */
    protected function applyRelations(&$query)
    {
        $query = $query->with($this->relations);
        return $query;
    }

    protected function resetRelations()
    {
        $this->relations = [];
    }
}
