<?php

require __DIR__ . '/../src/Loader.php';

use CorderoDigital\GQLQueryLoader\Loader;

$loader = new Loader;

$variables = [
    [
        'name' => 'id',
        'type' => 'Int!',
        'value' => 1
    ]
];

$query = $loader->loadQuery(__DIR__ . '/queries/getUser.gql', $variables)->query();

$query = json_encode($query, JSON_PRETTY_PRINT);

echo $query;
