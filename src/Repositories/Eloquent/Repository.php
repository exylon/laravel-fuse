<?php


namespace Exylon\Fuse\Repositories\Eloquent;


use Exylon\Fuse\Contracts\Appendable;
use Exylon\Fuse\Contracts\Relatable;
use Exylon\Fuse\Contracts\Repository as BaseRepository;
use Exylon\Fuse\Contracts\Transformable;
use Exylon\Fuse\Contracts\Validatable;
use Exylon\Fuse\Contracts\WithOptions;
use Exylon\Fuse\Repositories\Entity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory;
use InvalidArgumentException;

class Repository implements BaseRepository, Appendable, Relatable, Transformable, Validatable, WithOptions
{

    use Concerns\HasAppendableAttributes,
        Concerns\HasRelations,
        Concerns\CanValidate,
        Concerns\CanTransform,
        Concerns\HasOptions;

    /**
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder
     */
    protected $original;
    /**
     * @var \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Query\Builder
     */
    protected $query;


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

        if ($validator) {
            $this->setValidator($validator);
        }

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
        $this->query = $this->applyRelations($this->query);
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

        $this->query = $this->applyRelations($this->query);
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
        $this->runCreateValidation($attributes);

        $model = $this->original instanceof Builder ? $this->original->newModelInstance() : $this->original->newInstance();
        $model->forceFill($attributes);
        $model->save();
        $model->refresh();
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
        $this->runCreateValidation($attributes);

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

        $this->runUpdateValidation($data);

        $this->query = $this->applyRelations($this->query);
        $this->applyWhereClauses($where);
        $model = $this->query->firstOrFail();
        $model->forceFill($data);
        $model->save();
        $model->refresh();
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
        $this->runUpdateValidation($data);

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
        $this->query = $this->applyRelations($this->query);
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

        $this->query = $this->applyRelations($this->query);
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
     * Resets the query
     */
    protected function reset()
    {
        if ($this->original instanceof Builder) {
            $this->query = $this->original->newQuery();
        } else {
            $this->query = $this->original->newInstance();
        }
        $this->resetValidation();
        $this->resetOptions();
        $this->resetRelations();
        $this->resetAppends();
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

                if (!empty($options['parameters'])) {
                    //
                    // Special scenario since between's parameters should be add as raw
                    //
                    if (Str::lower($options['method']) === 'between') {
                        $parameters = array_merge($parameters, [$options['parameters']]);
                    } else {
                        $parameters = array_merge($parameters, Arr::wrap($options['parameters']));
                    }
                }

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
