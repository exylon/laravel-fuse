<?php


namespace Exylon\Fuse\Repositories\Database;


use Carbon\Carbon;
use Exylon\Fuse\Contracts\Repository as BaseRepository;
use Exylon\Fuse\Contracts\Transformable;
use Exylon\Fuse\Contracts\WithOptions;
use Exylon\Fuse\Repositories\Database\Concerns\CanTransform;
use Exylon\Fuse\Repositories\Database\Concerns\HasOptions;
use Exylon\Fuse\Support\Arr;

class Repository implements BaseRepository, Transformable, WithOptions
{

    use CanTransform, HasOptions;
    /**
     * @var \Illuminate\Database\DatabaseManager
     */
    protected $db;
    /**
     * @var string
     */
    protected $tableName;
    /**
     * @var string
     */
    private $primaryKeyName;
    /**
     * @var bool
     */
    private $withTimestamps;

    public function __construct(string $tableName, string $primaryKeyName = 'id', bool $withTimestamps = true)
    {
        $this->db = app('db');
        $this->tableName = $tableName;
        $this->primaryKeyName = $primaryKeyName;
        $this->withTimestamps = $withTimestamps;
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
        $results = $this->db->table($this->tableName)->get($columns)->map(function ($item) {
            return $this->transform((array)$item);
        });
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
        // TODO: Implement paginate() method.
    }

    /**
     * Returns a chunk of entities given the set of conditions
     *
     * @param array|callable $where
     * @param int            $limit
     * @param null           $page
     * @param array          $columns
     *
     * @return mixed
     */
    public function paginateWhere($where, int $limit, $page = null, array $columns = array('*'))
    {
        // TODO: Implement paginateWhere() method.
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
        $options = $this->getOptions();
        $this->applyTimestamp($attributes, Arr::get($options, 'column_created_at', 'created_at'));
        $this->applyTimestamp($attributes, Arr::get($options, 'column_updated_at', 'updated_at'));

        $id = $this->db->table($this->tableName)->insertGetId($attributes);
        $entity = $this->db->table($this->tableName)->find($id);

        return $this->transform((array)$entity);
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
        return $this->transform($attributes);
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
        // TODO: Implement update() method.
    }

    /**
     * Find and update an entity
     *
     * @param array|callable $where
     * @param array          $data
     *
     * @return mixed
     */
    public function updateWhere($where, array $data)
    {
        // TODO: Implement updateWhere() method.
    }

    /**
     * Find and update all matching entities. Returns the number of affected rows
     *
     * @param array|callable $where
     * @param array          $data
     *
     * @return int
     */
    public function updateAllWhere($where, array $data)
    {
        // TODO: Implement updateAllWhere() method.
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
        // TODO: Implement delete() method.
    }

    /**
     * Find and delete an entity
     *
     * @param array|callable $where
     *
     * @return boolean
     */
    public function deleteWhere($where)
    {
        // TODO: Implement deleteWhere() method.
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
        // TODO: Implement find() method.
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
        // TODO: Implement findBy() method.
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
        // TODO: Implement findAllBy() method.
    }

    /**
     * Find entity by where clauses
     *
     * @param array|callable $where
     * @param array          $columns
     *
     * @return mixed
     */
    public function findWhere($where, $columns = array('*'))
    {
        // TODO: Implement findWhere() method.
    }

    /**
     * Find entities by where clauses
     *
     * @param array|callable $where
     * @param array          $columns
     *
     * @return mixed
     */
    public function findAllWhere($where, $columns = array('*'))
    {
        // TODO: Implement findAllWhere() method.
    }

    /**
     * Checks whether an entity exists from the repository
     *
     * @param array|callable $where
     *
     * @return boolean
     */
    public function exists($where)
    {
        // TODO: Implement exists() method.
    }


    /**
     * @param array  $attributes
     * @param string $column
     */
    protected function applyTimestamp(array &$attributes, string $column)
    {
        if ($this->withTimestamps) {
            $attributes[$column] = Carbon::now();
        }
    }


}
