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
     * Deletes an entity from the repository
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function delete($id);

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
     * Load relations
     *
     * @param array|string $relations
     *
     * @return $this
     */
    public function with($relations);

    /**
     * Enables validation for create, make and update methods
     *
     * @return $this
     */
    public function withValidation();

    /**
     * Enables the transformer. If none is provided, the default transformer will be used
     *
     * @param \Exylon\Fuse\Contracts\Transformer|\Closure|mixed|null $transformer
     *
     * @return $this
     */
    public function withTransformer($transformer = null);

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
    public function setValidationRules(array $create, array $update = null);

    /**
     * Sets the default transformer
     *
     * @param \Exylon\Fuse\Contracts\Transformer|\Closure|mixed|null $transformer
     *
     * @return mixed
     */
    public function setDefaultTransformer($transformer);
}
