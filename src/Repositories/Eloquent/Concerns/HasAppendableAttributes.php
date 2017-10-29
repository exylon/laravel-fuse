<?php

namespace Exylon\Fuse\Repositories\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Model;

trait HasAppendableAttributes
{
    /**
     * @var array
     */
    protected $appends = [];

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
     * Appends additional attributes to the Eloquent model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return $this|\Illuminate\Database\Eloquent\Model
     */
    protected function applyAppends(Model &$model)
    {
        if (!empty($this->appends)) {
            return $model->append($this->appends);
        }
        return $model;
    }

    protected function resetAppends()
    {
        $this->appends = [];
    }

}
