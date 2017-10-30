<?php


namespace Exylon\Fuse\Support;


class Arr extends \Illuminate\Support\Arr
{

    public static function dotReverse(array $array)
    {
        $arr = [];
        foreach ($array as $key => $value) {
            \Illuminate\Support\Arr::set($arr, $key, $value);
        }
        return $arr;
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
            if (is_array($value) && \Illuminate\Support\Arr::isAssoc($value) && !empty($value)) {
                $results = array_merge($results, static::dot($value, $prepend . $key . '.'));
            } else {
                $results[$prepend . $key] = $value;
            }
        }

        return $results;
    }

    public static function only($array, $keys)
    {
        $keys = self::wrap($keys);
        $arr = [];
        foreach ($keys as $key) {
            $arr[$key] = self::get($array, $key);
        }

        return self::dotReverse($arr);

    }
}
