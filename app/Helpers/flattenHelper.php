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
