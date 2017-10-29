<?php


namespace Exylon\Fuse\Contracts;


interface Validatable
{

    /**
     * Enables validation for create, make and update methods
     *
     * @return $this
     */
    public function withValidation();


    /**
     * Sets the validation rules.
     * If update rules are not provided, it will have
     * the same value as create rules
     *
     * @param array      $create
     * @param array|null $update
     */
    public function setValidationRules(array $create, array $update = null);
}
