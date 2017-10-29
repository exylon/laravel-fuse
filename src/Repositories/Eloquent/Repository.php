<?php


namespace Exylon\Fuse\Repositories\Eloquent;


use Exylon\Fuse\Repositories\Entity;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;
use InvalidArgumentException;

class Repository implements \Exylon\Fuse\Contracts\Repository
{

    /**
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder
     */
    protected $original;
    /**
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder
     */
    protected $query;

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

    /**
     * @var array
     */
    protected $appends = [];

    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var \Illuminate\Validation\Factory
     */
    protected $validator;


    /**
     * Repository constructor.
     *
     * @param \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder $model
     * @param array                                                                  $options
     * @param \Illuminate\Validation\Factory|null                                    $validator
     */
    public function __construct($model, array $options = [], Factory $validator = null)
    {
        $this->original = $model;
        $this->validator = $validator;
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
        $this->applyRelations();
        if ($this->query instanceof Builder) {
            $results = $this->query->get($columns)->map(function ($item) {
                $item = $this->applyAppends($item);
                return $this->transform($item);
            });
        } else {
            $results = $this->query->all($columns)->map(function ($item) {
                $item = $this->applyAppends($item);
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

        $this->applyRelations();
        $this->applyWhereClauses($where);

        $paginator = $this->query->{$method}($limit, $columns, $pageName, $page);
        $paginator
            ->getCollection()
            ->transform(function ($item) {
                $item = $this->applyAppends($item);
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
        if ($this->enableValidation && !empty($this->createRules) && $this->validator !== null) {
            $this->validator->validate($attributes, $this->createRules);
        }

        $model = $this->original instanceof Builder ? $this->original->newModelInstance() : $this->original->newInstance();
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
        if ($this->enableValidation && !empty($this->createRules) && $this->validator !== null) {
            $this->validator->validate($attributes, $this->createRules);
        }

        $model = $this->original instanceof Builder ? $this->original->newModelInstance() : $this->original->newInstance();
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
        $id = $this->getModelKey($id);
        return $this->updateWhere([$this->getModelKeyName() => $id], $data);
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
        if ($this->enableValidation && !empty($this->updateRules) && $this->validator !== null) {
            $this->validator->validate($data, $this->updateRules);
        }
        $this->applyRelations();
        $this->applyWhereClauses($where);
        $model = $this->query->firstOrFail();
        $model->forceFill($data);
        $model->save();
        $model = $this->applyAppends($model);
        $this->reset();


        return $this->transform($model);
    }

    /**
     * Find and update all matching entities.
     *
     * @param array $where
     * @param array $data
     *
     * @return int
     */
    public function updateAllWhere(array $where, array $data)
    {
        if ($this->enableValidation && !empty($this->updateRules)) {
            $this->validator->validate($data, $this->updateRules);
        }

        $this->applyWhereClauses($where);
        $updated = $this->query->update($data);
        $this->reset();

        return $updated;
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
        $id = $this->getModelKey($id);
        return $this->deleteWhere([$this->getModelKeyName() => $id]);
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
        $this->applyWhereClauses($where);
        $model = $this->query->firstOrFail();
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
        $id = $this->getModelKey($id);
        return $this->findBy($this->getModelKeyName(), $id);
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
        $this->applyRelations();
        $this->applyWhereClauses($where);
        $model = $this->query->firstOrFail($columns);
        $model = $this->applyAppends($model);
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

        $this->applyRelations();
        $this->applyWhereClauses($where);
        $results = $this->query->get($columns)->map(function ($item) {
            $item = $this->applyAppends($item);
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
        $ret = $this->query->exists();
        $this->reset();
        return $ret;
    }

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
     * Enables validation for create, make and update methods
     *
     * @return $this
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
     * Overrides the default settings for this repository
     *
     * @param array $defaultOptions
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


    /**
     * Resets the query
     */
    protected function reset()
    {
        if ($this->original instanceof Builder) {
            $this->query = $this->original->newQuery();
        } else {
            $this->query = $this->original->newInstance();
        }
        $this->enableValidation = false;
        $this->options = [];
        $this->relations = [];
        $this->appends = [];
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

    /**
     * Find raw model object from the repository
     *
     * @param      $entity
     * @param bool $forceFresh
     * @param null $query
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|static
     */
    protected function findRawModel($entity, bool $forceFresh = false, $query = null)
    {
        $query = $query ?: $this->query;

        $class = $query instanceof Builder ? $query->newModelInstance() : $query;
        if ($entity instanceof $class) {
            return $forceFresh ? $entity->refresh() : $entity;
        }

        $entity = $this->getModelKey($entity);

        if (is_string($query)) {
            $query = app($query);
            if (!$query instanceof Model) {
                throw new InvalidArgumentException("Query must be an instance of Model");
            }
            $query = $query->newInstance();
        }

        return $query->where($this->getModelKeyName(), $entity)->firstOrFail();
    }

    /**
     * Gets the Model or Entity Key
     *
     * @param $entity
     *
     * @return mixed
     */
    protected function getModelKey($entity)
    {
        if ($entity instanceof Entity || $entity instanceof Model) {
            return $entity->getKey();
        }
        return $entity;
    }

    protected function getModelKeyName()
    {
        static $keyName;
        if (!is_null($keyName)) {
            return $keyName;
        }
        return $keyName = ($this->original instanceof Builder ? $this->original->newModelInstance()->getKeyName() : $this->original->getKeyName());
    }


    /**
     * @deprecated Use `findRawModel` instead
     *
     * @param      $model
     * @param bool $forceFresh
     *
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder|null|static
     */
    protected function _findOrFail($model, bool $forceFresh = false)
    {
        return $this->findRawModel($model, $forceFresh);
    }

    /**
     * Applies all added relations to the query
     */
    protected function applyRelations()
    {
        $this->query = $this->query->with($this->relations);
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

    /**
     * Apply all where clauses
     *
     * @param array $where
     * @param bool  $required
     */
    protected function applyWhereClauses(array $where, bool $required = false)
    {
        if ($required && empty($where)) {
            throw new InvalidArgumentException('Empty "where" clauses');
        }

        foreach ($where as $field => $value) {
            $orWhere = Str::startsWith($field, 'or-');
            $field = trim(str_replace_first('or-', '', $field), '-');

            if (is_array($value) && Arr::isAssoc($value)) {
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

                $this->query = call_user_func_array([
                    $this->query,
                    ($orWhere ? 'orWhere' : 'where') . title_case($options['method'])
                ], $parameters);
            } elseif (is_array($value)) {
                $this->query = call_user_func_array([
                    $this->query,
                    ($orWhere ? 'orWhere' : 'where')
                ], array_merge([$field], $value));
            } else {
                $this->query = $this->query->where($field, $value);
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
