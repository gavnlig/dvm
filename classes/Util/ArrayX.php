<?php

namespace Util;

class ArrayX {

    /**
     * Returns the input data as an array. Passing null to this function will
     * return an empty array.
     * @param mixed $in Any input value. Null will converted to an empty array
     * @return type
     */
    public static function toArray($in) {
        if ($in === null)
            return array();
        else
            return is_array($in) ? $in : array($in);
    }

    /**
     * Searches the array recursive for a given value and returns true if successful.
     * @param mixed $needle The searched value.
     * @param array $haystack The array.
     * @return bool
     */
    public static function array_search_recursive($needle, $haystack) {
        foreach ($haystack as $key => $value) {
            if ($needle === $value || ( is_array($value) && static::array_search_recursive($needle, $value) !== false)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Removes null values and other empty arrays recursively from an array.
     * @param array $haystack
     * @return array
     */
    public static function trim($haystack) {
        foreach($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = static::trim($value);
                $value = $haystack[$key];
            }
            
            if($value === null || (is_array($value) && count($value)==0)) {
                unset($haystack[$key]);
            }
        }
        
        return $haystack;
    }

}
