<?php


namespace Exylon\Fuse\Support;


class Str extends \Illuminate\Support\Str
{
    /**
     * @param array $pairs
     * @param       $subject
     *
     * @return string
     */

    public static function replaceAssoc(array $pairs, $subject): string
    {
        return str_replace(array_keys($pairs), array_values($pairs), $subject);
    }

    /**
     * Generates random hexadecimal string with specified length
     *
     * @param int $length
     *
     * @return string
     */
    public static function randomHex(int $length): string
    {

        $bytes = random_bytes(($length / 2) + 1);
        $out = bin2hex($bytes);

        return substr($out, 0, $length);
    }

    /**
     * Generates random integer string with specific length
     *
     * @param int    $length
     * @param int    $min
     * @param string $pad
     *
     * @return string
     */
    public static function randomInt(int $length, int $min = 0, string $pad = '0'): string
    {

        $pad = $pad ?: '0';
        $max = pow(10, $length) - 1;
        if ($max > PHP_INT_MAX) {
            $max = PHP_INT_MAX;
        }
        $out = random_int($min, $max);


        return str_pad($out, $length, $pad, STR_PAD_LEFT);
    }

    public static function properCase(string $str, $delimiters = '_'): string
    {
        return ucwords(str_replace($delimiters, ' ', $str), " \t\r\n\f\v-");
    }
}
