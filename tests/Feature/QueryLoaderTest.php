<?php

use CorderoDigital\QueryLoader\Loader;

beforeEach(function () {
    $this->root = __DIR__ . '/../';
});

test('it can load .gql file types', function () {
    $query = Loader::load($this->root . 'queries/simplePingTest.gql');

    $expected = <<<GQL
query Ping {
    ping
}
GQL;

    expect($query)->toBe($expected);
});

test('it can load .graphql file types', function () {
    $query = Loader::load($this->root . 'queries/simplePingTest.graphql');

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

test('it can load a query from a file with mixed file extension types', function () {
    $query = Loader::load($this->root . 'queries/singleMixedFragmentFileExtensionIncludeTest.gql');

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

test('it can load a query with no variables', function () {
    $query = Loader::load($this->root . 'queries/simplePingTest.gql');

    $expected = <<<GQL
query Ping {
    ping
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
    user(\$id: 123, \$status: "active") {
        id
        name
        email
    }
    users(\$status: "active") {
        id
        name
        email
    }
}
GQL;

    $query = Loader::load($this->root . 'queries/variableInjectionTest.gql', $variables);

    expect($query)->toBe($expected);

});

test('it throws an exception if variable is set in query but not in variables array', function () {
    $variables = [
        [
            'name' => 'id',
            'type' => 'ID!',
            'value' => 123,
        ],
    ];

    $query = Loader::load($this->root . 'queries/variableNotInArrayExceptionTest.gql', $variables);

})->throws(Exception::class, 'Variable $status is set in query but not in variables array');

test('it throws an exception if fragment file is missing', function () {

    $query = Loader::load($this->root . 'queries/missingFragmentExceptionTest.gql');

})->throws(Exception::class, 'Fragment file not found: fragments/missingFragment.gql');
