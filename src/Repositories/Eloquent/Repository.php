<?php


namespace Exylon\Fuse\Repositories\Eloquent;


use Exylon\Fuse\Repositories\Entity;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
    protected $defaultOptions;

    /**
     * @var array
     */
    protected $options = [];


    public function __construct(Model $model, array $options = [])
    {
        $this->originalModel = $model;
        $this->reset();
        $this->setDefaultOptions($options);
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
     * @param null  $page
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate(int $limit, $page = null, array $columns = array('*'))
    {
        return $this->paginateWhere([], $limit, $page, $columns);
    }

    /**
     * Returns a chunk of entities given the set of conditions
     *
     * @param array $where
     * @param int   $limit
     * @param null  $page
     * @param array $columns
     *
     * @return mixed
     */
    public function paginateWhere(array $where, int $limit, $page = null, array $columns = array('*'))
    {
        $options = $this->getOptions();
        $pageName = $options['page_name'];
        $method = $this->resolvePaginationMethod($options['pagination_method']);

        $this->applyWhereClauses($where);

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

        $model = $this->originalModel->newInstance();
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

        $model = $this->originalModel->newInstance();
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
        $model = $this->_findOrFail($id);
        $model->forceFill($data);
        $model->save();
        $this->reset();

        return $this->transform($model);
    }


    /**
     * Find and update an entity
     *
     * @param array $where
     * @param array $data
     *
     * @return mixed
     */
    public function updateWhere(array $where, array $data)
    {
        $entity = $this->findWhere($where);
        return $this->update($entity, $data);
    }

    /**
     * Deletes an entity from the repository
     *
     * @param mixed $id
     *
     * @return boolean
     */
    public function delete($id)
    {
        $model = $this->_findOrFail($id);
        $deleted = $model->delete();
        $this->reset();

        return $deleted;
    }


    /**
     * Find and delete an entity
     *
     * @param array $where
     *
     * @return boolean
     */
    public function deleteWhere(array $where)
    {
        $entity = $this->findWhere($where);
        return $this->delete($entity);
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
        $model = $this->_findOrFail($id, true);
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
        return $this->findWhere([
            $field => $value
        ], $columns);
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
        return $this->findAllWhere([
            $field => $value
        ], $columns);
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
        $this->applyWhereClauses($where);
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

        $this->applyWhereClauses($where);
        $results = $this->model->get($columns)->map(function ($item) {
            return $this->transform($item);
        });
        $this->reset();

        return $results;
    }

    /**
     * Checks whether an entity exists from the repository
     *
     * @param array $where
     *
     * @return boolean
     */
    public function exists(array $where)
    {
        $this->applyWhereClauses($where);
        $ret = $this->model->exists();
        $this->reset();
        return $ret;
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
     * Overrides the default settings for this repository
     *
     * @param array $defaultOptions
     *
     * @return mixed
     */
    public function setDefaultOptions(array $defaultOptions)
    {
        $this->defaultOptions = $defaultOptions;
    }


    /**
     * Use options
     *
     * @param array $options
     *
     * @return $this
     */
    public function withOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    protected function reset()
    {
        $this->model = $this->originalModel->newInstance();
        $this->enableValidation = false;
        $this->options = [];
    }

    protected function resetTransformer()
    {
        $this->transformer = null;
        $this->enableTransformer = true;
    }

    protected function transform(Model $model)
    {
        if ($this->enableTransformer) {
            if (($callback = $this->getTransformerCallback($this->transformer, $this->defaultTransformer)) !== null) {
                $result = $this->executeTransformer($callback, $model);
            } else {
                $result = new Entity($model->getKey(), $model->toArray());
            }
        } else {
            $result = new Entity($model->getKey(), $model->toArray());
        }

        $this->resetTransformer();

        return $result;
    }

    private function getTransformerCallback($transformer, $defaultTransformer = null)
    {
        if (is_callable($transformer) || is_string($transformer) || is_array($transformer)) {
            return $transformer;
        } elseif (is_object($transformer)) {
            return [$transformer, 'transform'];
        }
        return $defaultTransformer === null ? null : $this->getTransformerCallback($defaultTransformer);
    }

    private function executeTransformer($callback, $model)
    {
        if (is_callable($callback)) {
            return call_user_func($callback, $model);
        }
        return Container::getInstance()->call($callback);
    }


    protected function _findOrFail($model, bool $forceFresh = false)
    {
        if ($model instanceof $this->originalModel) {
            return $forceFresh ? $model->fresh() : $model;
        }
        if ($model instanceof Entity) {
            $model = $model->getKey();
        }
        return $this->model->findOrFail($model);
    }


    protected function applyWhereClauses(array $where)
    {
        foreach ($where as $field => $value) {
            $orWhere = Str::startsWith($field, 'or-');
            $field = trim(str_replace_first('or-', '', $field), '-');
            if (is_assoc($value)) {
                $options = array_merge([
                    'method'     => '',
                    'operation'  => null,
                    'parameters' => null
                ], $value);

                $parameters = [];
                $parameters[] = $field;
                if (empty($options['method']) && isset($options['operation'])) {
                    $parameters[] = $options['operation'];
                }
                $parameters[] = $options['parameters'];

                $this->model = call_user_func_array([
                    $this->model,
                    ($orWhere ? 'orWhere' : 'where') . title_case($options['method'])
                ], $parameters);
            } elseif (is_array($value)) {
                $this->model = call_user_func_array([
                    $this->model,
                    ($orWhere ? 'orWhere' : 'where')
                ], array_merge([$field], $value));
            } else {
                $this->model = $this->model->where($field, $value);
            }
        }

    }


    protected function getOptions()
    {
        return array_merge(
            config('fuse.repository') ?: [],
            $this->defaultOptions ?: [],
            $this->options ?: []);
    }

    protected function resolvePaginationMethod($method)
    {
        switch ($method) {
            case null:
            case 'length_aware':
                $method = 'paginate';
                break;
            case 'simple':
                $method = 'simplePaginate';
                break;
        }
        return $method;
    }
}
