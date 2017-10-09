<?php


namespace Exylon\Fuse\Support;


class Arr
{

    public static function dotReverse(array $array)
    {
        $arr = [];
        foreach ($array as $key => $value) {
            \Illuminate\Support\Arr::set($arr, $key, $value);
        }
        return $arr;
    }

    public static function isAssoc($array)
    {
        if (!is_array($array)) {
            return false;
        }
        foreach ($array as $key => $value) {
            if (!is_int($key)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Flatten a multi-dimensional associative array with dots and skips linear arrays
     *
     * @param  array  $array
     * @param  string $prepend
     *
     * @return array
     */
    public static function dot($array, $prepend = '')
    {
        $results = [];

        foreach ($array as $key => $value) {
            if (is_assoc($value) && !empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }
}
