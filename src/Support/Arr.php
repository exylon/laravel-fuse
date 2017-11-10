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

    /**
     * Overridden function to support dot notation
     *
     * @param array        $array
     * @param array|string $keys
     *
     * @return array
     */
    public static function only($array, $keys)
    {
        $keys = self::wrap($keys);
        $arr = [];
        foreach ($keys as $key) {
            $arr[$key] = self::get($array, $key);
        }

        return self::dotReverse($arr);

    }

    /**
     * Checking if the key exists and not empty.
     *
     * Note that this function evaluates based on
     * `empty()` (@link http://php.net/manual/en/function.empty.php) function
     *
     * @param $array
     * @param $key
     *
     * @return bool
     */
    public static function filled($array, $key)
    {
        return self::has($array, $key) && !empty(self::get($array, $key));
    }
}
