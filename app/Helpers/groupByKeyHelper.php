<?php

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
