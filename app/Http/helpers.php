<?php

/**
 * Flattens out a multi-dimentional array
 *
 * @params array $elements A multi-dimentional array
 * @params int $depth Index showing the array depth
 *
 * @return array $result Returns flattened array
 */
function flatten($elements, $depth) {
    $result = array();

    foreach ($elements as $key => &$element) {
        if (isset($depth) == true) {
            $element['depth'] = $depth;
        }

        if (isset($element['children'])) {
            $children = $element['children'];
            unset($element['children']);
        } else {
            $children = null;
        }

        $result[$key] = $element;

        if (isset($children)) {
            $result = array_merge($result, flatten($children, $depth + 1));
        }
    }

    return $result;
}

/**
 * Extracts a resource's name from it's URI
 *
 * @params string $uri The resource's URI
 * @return string $name The resource's name
 */
function getResourceNameFromURI($uri)
{
    $delimiter = strstr($uri, '#') ? '#' : '/';
    $tmp = explode($delimiter, $uri);
    $name = array_pop($tmp);
    return $name;
}

/**
 * Groups an array by key
 *
 * @params array $array The given array
 * @params string $key The key to be used for grouping
 * @return string $array The array grouped by the key given
 */
 function groupByKey($array, $key) {
     $return = array();
     foreach($array as $val) {
         $return[$val[$key]][] = $val;
     }
     return $return;
 }
