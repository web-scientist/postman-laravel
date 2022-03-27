<?php

namespace WebScientist\PostmanLaravel\Concerns;

trait Arr
{
    public static function undot($array)
    {
        $results = [];

        foreach ($array as $key => $value) {
            self::set($results, $key, $value);
        }

        return $results;
    }

    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $key = str_replace('*', '0', $key);

        $keys = explode('.', $key);

        foreach ($keys as $i => $key) {
            if (count($keys) === 1) {
                break;
            }

            unset($keys[$i]);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.

            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }


            $array = &$array[$key];
        }


        $array[array_shift($keys)] = $value;

        return $array;
    }
}
