<?php

use GraphQL\Type\Definition\BooleanType;
use GraphQL\Type\Definition\FloatType;
use GraphQL\Type\Definition\IntType;
use GraphQL\Type\Definition\StringType;

return [
    // Defaults
    'default'   => StringType::class,

    // Integers
    'int'       => IntType::class,
    'float'     => FloatType::class,
    'double'    => IntType::class,

    // Strings
    'text'      => StringType::class,
    'char'      => StringType::class,
    'varchar'   => StringType::class,

    // Booleans
    'tinyint'   => BooleanType::class,

    // Times
    'timestamp' => StringType::class,
];