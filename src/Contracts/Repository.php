<?php


namespace Exylon\Fuse\Contracts;


interface Repository
{
    /**
     * Returns all entities of the repository
     *
     * @param array $columns
     *
     * @return \Illuminate\Support\Collection
     */
    public function all(array $columns = array('*'));

    /**
     * Returns a chunk of entities
     *
     * @param int   $limit
     * @param null  $page
     * @param array $columns
     *
     * @return mixed
     */
    public function paginate(int $limit, $page = null, array $columns = array('*'));


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
    public function paginateWhere(array $where, int $limit, $page = null, array $columns = array('*'));

    /**
     * Creates and persists an entity
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function create(array $attributes);

    /**
     * Creates an entity without persisting
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function make(array $attributes);

    /**
     * Updates the given entity
     *
     * @param array  $data
     * @param  mixed $id
     *
     * @return mixed
     */
    public function update($id, array $data);

    /**
     * Find and update an entity
     *
     * @param array $where
     * @param array $data
     *
     * @return mixed
     */
    public function updateWhere(array $where, array $data);

    /**
     * Find and update all matching entities. Returns the number of affected rows
     *
     * @param array $where
     * @param array $data
     *
     * @return int
     */
    public function updateAllWhere(array $where, array $data);

    /**
     * Deletes an entity from the repository
     *
     * @param mixed $id
     *
     * @return boolean
     */
    public function delete($id);

    /**
     * Find and delete an entity
     *
     * @param array $where
     *
     * @return boolean
     */
    public function deleteWhere(array $where);

    /**
     * Find entity by id
     *
     * @param mixed $id
     * @param array $columns
     *
     * @return mixed
     */
    public function find($id, array $columns = array('*'));

    /**
     * Find entity by field
     *
     * @param mixed $field
     * @param mixed $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBy($field, $value, $columns = array('*'));


    /**
     * Find entities by field
     *
     * @param mixed $field
     * @param mixed $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findAllBy($field, $value, $columns = array('*'));


    /**
     * Find entity by where clauses
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhere(array $where, $columns = array('*'));

    /**
     * Find entities by where clauses
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findAllWhere(array $where, $columns = array('*'));

    /**
     * Checks whether an entity exists from the repository
     *
     * @param array $where
     *
     * @return boolean
     */
    public function exists(array $where);

}
