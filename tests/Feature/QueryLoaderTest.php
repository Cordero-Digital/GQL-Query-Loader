<?php

use CorderoDigital\QueryLoader\Loader;

beforeEach(function () {
    $this->root = __DIR__ . '/../';
});

test('it can load a query from a file', function () {
    $query = Loader::load($this->root . 'queries/simplePingTest.gql');

    $expected = <<<GQL
query Ping {
    ping
}
GQL;

    expect($query)->toBe($expected);
});

test('it can load a query from a file with includes', function () {
    $query = Loader::load($this->root . 'queries/singleFragmentIncludeTest.gql');

    $expected = <<<GQL
fragment SimpleFragment on Object {
    id
    name
    email
}

query testSimpleSingleFragment {
    object {
        ...SimpleFragment
    }
}
GQL;

    expect($query)->toBe($expected);
});

test('it can dynamically inject variables from an array', function () {
    $variables = [
        [
            'name' => 'id',
            'type' => 'ID!',
            'value' => 123,
        ],
        [
            'name' => 'status',
            'type' => '[String!]',
            'value' => 'active',
        ],
    ];

    $expected = <<<GQL
query userQuery(\$id: ID!, \$status: [String!]) {
    user(id: 123) {
        id
        name
        email
    }
    users(status: "active") {
        id
        name
        email
    }
}
GQL;

    $query = Loader::load($this->root . 'queries/variableInjectionTest.gql', $variables);

    expect($query)->toBe($expected);

});
