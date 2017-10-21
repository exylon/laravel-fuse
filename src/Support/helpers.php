<?php

if (!function_exists('str_replace_assoc')) {
    /**
     * @param array $pairs
     * @param       $subject
     *
     * @return string
     */
    function str_replace_assoc(array $pairs, $subject): string
    {
        return \Exylon\Fuse\Support\Str::replaceAssoc($pairs, $subject);
    }
}

if (!function_exists('validate')) {

    /**
     * Validates and extracts validated data
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     *
     * @return array
     */
    function validate(array $data, array $rules, $messages = array(), $customAttributes = array())
    {
        Illuminate\Support\Facades\Validator::validate($data, $rules, $messages, $customAttributes);

        return array_only($data, collect($rules)->keys()->map(function ($rule) {
            return str_contains($rule, '.') ? explode('.', $rule)[0] : $rule;
        })->unique()->toArray());
    }
}


if (!function_exists('str_random_hex')) {
    /**
     * Generates random hexadecimal string with specified length
     *
     * @param int $length
     *
     * @return string
     */
    function str_random_hex(int $length): string
    {
        return \Exylon\Fuse\Support\Str::randomHex($length);
    }
}

if (!function_exists('str_random_int')) {
    /**
     * Generates random integer string with specific length
     *
     * @param int    $length
     * @param int    $min
     * @param string $pad
     *
     * @return string
     */
    function str_random_int(int $length, int $min = 0, string $pad = '0'): string
    {
        return \Exylon\Fuse\Support\Str::randomInt($length, $min, $pad);
    }
}


if (!function_exists('proper_case')) {
    /**
     * Converts a string to proper cased string
     *
     * @param string $str
     * @param string $delimiters
     *
     * @return string
     */
    function proper_case(string $str, $delimiters = '_'): string
    {
        return \Exylon\Fuse\Support\Str::properCase($str, $delimiters);
    }
}


if (!function_exists('array_dot_reverse')) {
    /**
     * Converts dot-noted array to regular associative array
     *
     * @param array $dotArray
     *
     * @return array
     */
    function array_dot_reverse(array $dotArray)
    {
        return \Exylon\Fuse\Support\Arr::dotReverse($dotArray);
    }
}


if (!function_exists('has_trait')) {
    /**
     * Checks if an object or class has the given trait
     *
     * @param        $object
     * @param string $trait
     *
     * @return bool
     */
    function has_trait($object, string $trait)
    {
        return array_key_exists($trait, class_uses($object));
    }
}

