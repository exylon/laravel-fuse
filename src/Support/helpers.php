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
        return str_replace(array_keys($pairs), array_values($pairs), $subject);
    }
}

if (!function_exists('validate')) {

    /**
     * Shorthand validation
     *
     * @param array $data
     * @param array $rules
     * @param array $messages
     * @param array $customAttributes
     */
    function validate(array $data, array $rules,$messages = array(), $customAttributes = array())
    {
        Illuminate\Support\Facades\Validator::validate($data, $rules,$messages,$customAttributes);
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
        $bytes = random_bytes(($length / 2) + 1);
        $out = bin2hex($bytes);

        return substr($out, 0, $length);
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
        $pad = $pad ?: '0';
        $max = pow(10, $length) - 1;
        if ($max > PHP_INT_MAX) {
            $max = PHP_INT_MAX;
        }
        $out = random_int($min, $max);


        return str_pad($out, $length, $pad, STR_PAD_LEFT);
    }
}


if (!function_exists('snake_to_title_case')) {
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
        return ucwords(str_replace($delimiters, ' ', $str), " \t\r\n\f\v-");
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

