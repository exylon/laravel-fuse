<?php

if (!function_exists('str_replace_assoc')) {
    /**
     * @param array $pairs
     * @param       $subject
     *
     * @return mixed
     */
    function str_replace_assoc(array $pairs, $subject)
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
     */
    function validate(array $data, array $rules)
    {
        Illuminate\Support\Facades\Validator::validate($data, $rules);
    }
}

if (!function_exists('random_hex_string')) {
    /**
     * Generates random hexadecimal string with specified length
     *
     * @param int $length
     *
     * @return string
     */
    function random_hex_string($length)
    {
        $bytes = random_bytes(($length / 2) + 1);
        $out = bin2hex($bytes);

        return substr($out, 0, $length);
    }
}

if (!function_exists('random_int_string')) {
    /**
     * Generates random integer string with specific length
     *
     * @param int    $length
     * @param int    $min
     * @param string $pad
     *
     * @return string
     */
    function random_int_string($length, $min = 0, $pad = '0')
    {
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
     * Convert snake-cased string to proper title-cased string
     *
     * @param string $str
     *
     * @return string
     */
    function snake_to_title_case($str)
    {
        return ucwords(str_replace('_', ' ', $str));
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


if (!function_exists('is_assoc')) {

    /**
     * Checks if the given array is an associative array
     *
     * @param mixed $array
     *
     * @return bool
     */
    function is_assoc($array)
    {
        return \Exylon\Fuse\Support\Arr::isAssoc($array);
    }
}

