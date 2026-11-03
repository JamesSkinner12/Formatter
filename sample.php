<?php

require_once(__DIR__ . "/vendor/autoload.php");
use JamesSkinner12\Formatter\SampleFormatter;

// Load up the silly sample data set provided
$data = json_decode(file_get_contents(__DIR__ . "/sample.json"), true);
$class = new SampleFormatter($data);

// This will return the second-level id records from the array
$ids = $class->getItem("1|id");

/**
Array
(
    [0] => 1
    [1] => 2
    [2] => 3
)
*/

$flattenedStructure = $class->pipeSchema();

/**
Array
(
    [0|id] => integer
    [0|username] => string
    [0|details|hair] => string
    [0|details|eyes] => string
    [0|details|stench] => string
    [0|name|first] => string
    [0|name|last] => string
    [1|id] => integer
    ....etc......
)
*/
