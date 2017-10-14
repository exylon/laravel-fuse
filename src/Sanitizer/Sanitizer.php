<?php


namespace Exylon\Fuse\Sanitizer;


use Exylon\Fuse\Support\Arr;
use Illuminate\Container\Container;
use Illuminate\Support\Str;

class Sanitizer
{

    /**
     * @var array
     */
    protected $globalRules = [];

    /**
     * @var array
     */
    protected $registeredSanitizers = [];


    /**
     * Sets the default/global sanitizer rules
     *
     * @param array $sanitizers
     */
    public function setGlobalRules(array $sanitizers)
    {
        $this->globalRules = Arr::dot($sanitizers);
    }

    /**
     * Run sanitation on an associative array data
     *
     * @param       $data
     * @param array $rules
     * @param bool  $ignoreGlobalRules
     *
     * @return array
     */
    public function sanitize($data, $rules = [], $ignoreGlobalRules = false)
    {
        $rules = Arr::dot($rules);
        $data = Arr::dot($data);
        $ignoreGlobalRules = $ignoreGlobalRules || empty($this->globalRules);

        foreach ($data as $field => $value) {

            // Global Wildcard
            if (!$ignoreGlobalRules && array_key_exists('*', $this->globalRules)) {
                $data[$field] = $this->sanitizeValue($data[$field], $this->globalRules['*']);
            }

            // Global Rules
            if (!$ignoreGlobalRules && array_key_exists($field, $this->globalRules)) {
                $data[$field] = $this->sanitizeValue($data[$field], $this->globalRules[$field]);
            }

            // Field Wildcard
            if (array_key_exists('*', $rules)) {
                $data[$field] = $this->sanitizeValue($data[$field], $rules['*']);
            }

            // Field Rules
            if (array_key_exists($field, $rules)) {
                $data[$field] = $this->sanitizeValue($data[$field], $rules[$field]);
            }
        }

        return array_dot_reverse($data);
    }


    /**
     * Run sanitation on a value
     *
     * @param mixed $value
     * @param mixed $rules
     *
     * @return mixed
     */
    public function sanitizeValue($value, $rules)
    {
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }
        $rules = \Illuminate\Support\Arr::wrap($rules);
        foreach ($rules as $rule) {
            if (is_string($rule) && Str::contains($rule, ':')) {

                list($function, $parameters, $dataType) = array_pad(explode(':', $rule), 3, null);

                $value = $this->apply(
                    $value,
                    $function,
                    Str::contains($parameters, ',') ? explode(',', $parameters) : $parameters,
                    $dataType
                );
            } else {
                $value = $this->apply($value, $rule);
            }
        }

        return $value;
    }

    protected function apply($value, $rule, $parameters = null, $dataType = null)
    {
        $parameters = \Illuminate\Support\Arr::wrap($parameters ?: []);
        $dataType = $dataType ?: 'string';

        static $allowedDataTypes = [
            'string',
            'array',
            'int',
            'float',
            'double',
            'long',
            'numeric',
            'integer'
        ];
        // Let's skip sanitation rule if it's not applicable to the data type
        if (is_string($rule) && in_array($dataType, $allowedDataTypes) && !call_user_func('is_' . $dataType, $value)) {
            return $value;
        }

        if (!empty($this->registeredSanitizers) && array_key_exists($rule, $this->registeredSanitizers)) {
            $rule = $this->registeredSanitizers[$rule];
        }

        return Container::getInstance()->call(is_object($rule) && !is_callable($rule) ? [$rule, 'sanitize'] : $rule,
            array_merge([$value], $parameters));
    }

    public function register(string $name, $callback)
    {
        $this->registeredSanitizers[$name] = $callback;
    }

}
