<?php


namespace Exylon\Fuse\Repositories\Eloquent;


use Exylon\Fuse\Repositories\Entity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Repository implements \Exylon\Fuse\Contracts\Repository
{

    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $originalModel;
    /**
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * @var array
     */
    protected $updateRules = [];

    /**
     * @var array
     */
    protected $createRules = [];

    /**
     * @var bool
     */
    protected $enableValidation = false;

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
     * @var array
     */
    protected $options;


    public function __construct(Model $model, array $options = [])
    {
        $this->originalModel = $model;
        $this->reset();
        $this->setOptions($options);
    }


    /**
     * Returns all entities of the repository
     *
     * @param array $columns
     *
     * @return \Illuminate\Support\Collection
     */
    public function all(array $columns = array('*'))
    {
        if ($this->model instanceof Builder) {
            $results = $this->model->get($columns)->map(function ($item) {
                return $this->transform($item);
            });
        } else {
            $results = $this->model->all($columns)->map(function ($item) {
                return $this->transform($item);
            });
        }
        $this->reset();
        return $results;
    }

    /**
     * Returns a chunk of entities
     *
     * @param int   $limit
     * @param array $columns
     * @param null  $page
     *
     * @return mixed
     */
    public function paginate(int $limit, array $columns = array('*'), $page = null)
    {
        $pageName = $this->options['page_name'];
        $method = $this->options['pagination_method'];

        switch ($method) {
            case null:
            case 'length_aware':
                $method = 'paginate';
                break;
            case 'simple':
                $method = 'simplePaginate';
                break;
        }

        $paginator = $this->model->{$method}($limit, $columns, $pageName, $page);
        $paginator
            ->getCollection()
            ->transform(function ($item) {
                return $this->transform($item);
            });

        $this->reset();

        return $paginator;
    }

    /**
     * Creates and persists an entity
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes)
    {
        if ($this->enableValidation && !empty($this->createRules)) {
            \Validator::validate($attributes, $this->createRules);
        }

        $model = $this->model->newInstance();
        $model->forceFill($attributes);
        $model->save();
        $this->reset();

        return $this->transform($model);
    }

    /**
     * Creates an entity without persisting
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function make(array $attributes)
    {
        if ($this->enableValidation && !empty($this->createRules)) {
            \Validator::validate($attributes, $this->createRules);
        }

        $model = $this->model->newInstance();
        $model->forceFill($attributes);
        $this->reset();

        return $this->transform($model);
    }

    /**
     * Updates the given entity
     *
     * @param array  $data
     * @param  mixed $id
     *
     * @return mixed
     */
    public function update($id, array $data)
    {
        if ($this->enableValidation && !empty($this->updateRules)) {
            \Validator::validate($data, $this->updateRules);
        }
        $model = $id instanceof $this->model ? $id : $this->model->findOrFail($id);
        $model->forceFill($data);
        $model->save();
        $this->reset();

        return $this->transform($model);
    }

    /**
     * Deletes an entity from the repository
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function delete($id)
    {
        $model = $this->model->findOrFail($id);
        $deleted = $model->delete();
        $this->reset();

        return $deleted;
    }

    /**
     * Find entity by id
     *
     * @param mixed $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, array $columns = array('*'))
    {
        if (!($id instanceof $this->model)) {
            $model = $this->model->findOrFail($id, $columns);
        } else {
            $model = $id;
        }
        $this->reset();

        return $this->transform($model);
    }

    /**
     * Find entity by field
     *
     * @param mixed $field
     * @param mixed $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($field, $value, $columns = array('*'))
    {
        $model = $this->model->where($field, $value)->firstOrFail($columns);
        $this->reset();

        return $this->transform($model);
    }

    /**
     * Find entities by field
     *
     * @param mixed $field
     * @param mixed $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findAllBy($field, $value, $columns = array('*'))
    {
        $results = $this->model->where($field, $value)->get($columns)->map(function ($item) {
            return $this->transform($item);
        });
        $this->reset();

        return $results;
    }

    /**
     * Find entity by where clauses
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhere(array $where, $columns = array('*'))
    {
        foreach ($where as $field => $value) {
            $this->model = $this->model->where($field, $value);
        }
        $model = $this->model->firstOrFail($columns);
        $this->reset();

        return $this->transform($model);
    }

    /**
     * Find entities by where clauses
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findAllWhere(array $where, $columns = array('*'))
    {
        foreach ($where as $field => $value) {
            $this->model = $this->model->where($field, $value);
        }
        $results = $this->model->get($columns)->map(function ($item) {
            return $this->transform($item);
        });
        $this->reset();

        return $results;
    }

    /**
     * Load relations
     *
     * @param array|string $relations
     *
     * @return \Exylon\Fuse\Contracts\Repository
     */
    public function with($relations)
    {
        $this->model = $this->model->with($relations);
        return $this;

    }

    /**
     * Enables validation for create, make and update methods
     *
     * @return \Exylon\Fuse\Contracts\Repository
     */
    public function withValidation()
    {
        $this->enableValidation = true;
        return $this;
    }

    /**
     * Sets the validation rules.
     * If update rules are not provided, it will have
     * the same value as create rules
     *
     * @param array      $create
     * @param array|null $update
     *
     * @return mixed
     */
    public function setValidationRules(array $create, array $update = null)
    {
        $this->createRules = $create;
        if ($update) {
            $this->updateRules = $update;
        } else {
            $this->updateRules = $create;
        }
    }


    /**
     * Enables the transformer. If none is provided, the default transformer will be used
     *
     * @param \Exylon\Fuse\Contracts\Transformer|\Closure|mixed $transformer
     *
     * @return \Exylon\Fuse\Contracts\Repository
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
     * @return \Exylon\Fuse\Contracts\Repository
     */
    public function withoutTransformer()
    {
        $this->enableTransformer = false;
    }

    /**
     * Sets the default transformer
     *
     * @param \Exylon\Fuse\Contracts\Transformer|\Closure|mixed|null $transformer
     *
     * @return mixed
     */
    public function setDefaultTransformer($transformer)
    {
        $this->defaultTransformer = $transformer;
    }

    /**
     * Resets the model builder
     */
    protected function reset()
    {
        $this->model = $this->originalModel->newInstance();
        $this->enableValidation = false;
    }

    protected function resetTransfomer()
    {
        $this->transformer = null;
        $this->enableTransformer = true;
    }


    /**
     * Override this method if you do not intend to return a pure Eloquent model
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return mixed
     */
    protected function transform(Model $model)
    {
        if ($this->enableTransformer) {
            if (($callback = $this->getTransformerCallback($this->transformer)) !== null) {
                $result = call_user_func($callback, $model);
            } elseif ($model instanceof $this->model && ($callback = $this->getTransformerCallback($this->defaultTransformer)) !== null) {
                $result = call_user_func($callback, $model);
            } else {
                $result = new Entity($model->toArray());
            }
        } else {
            $result = new Entity($model->toArray());
        }

        $this->resetTransfomer();

        return $result;
    }

    private function getTransformerCallback(&$transformer)
    {
        if (is_callable($transformer)) {
            return $transformer;
        } elseif (method_exists($transformer, 'transform')) {
            return array($transformer, 'transform');
        }
        return null;
    }

    /**
     * Overrides the default settings for this repository
     *
     * @param array $options
     *
     * @return mixed
     */
    public function setOptions(array $options)
    {
        $options = array_merge(config('fuse.repository'), $options);
    }
}
