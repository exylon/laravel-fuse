<?php


namespace Exylon\Fuse\Repositories\Eloquent\Concerns;


use Illuminate\Validation\Factory as Validator;

trait CanValidate
{


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
     * @var \Illuminate\Validation\Factory
     */
    protected $validator;

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

    protected function setValidator(Validator $validator)
    {
        $this->validator = $validator;
    }

    protected function runCreateValidation(array $attributes)
    {
        if ($this->enableValidation && !empty($this->createRules) && $this->validator !== null) {
            $this->validator->validate($attributes, $this->createRules);
        }
    }

    protected function runUpdateValidation(array $attributes)
    {
        if ($this->enableValidation && !empty($this->updateRules) && $this->validator !== null) {
            $this->validator->validate($attributes, $this->updateRules);
        }
    }

    protected function resetValidation()
    {
        $this->enableValidation = false;
    }

}
