<?php

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
