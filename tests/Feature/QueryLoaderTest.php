<?php

use CorderoDigital\GQLQueryLoader\Loader;

beforeEach(function () {
    $this->root = __DIR__ . '/../';
    $this->loader = new Loader;
});

test('it can load .gql file types', function () {
    $query = $this->loader->loadQuery($this->root . 'queries/simplePingTest.gql')->query();

    $expected = <<<GQL
query Ping {
    ping
}
GQL;

    expect($query)->toBe($expected);
});

test('it can load .graphql file types', function () {
    $query = $this->loader->loadQuery($this->root . 'queries/simplePingTest.graphql')->query();

    $expected = <<<GQL
query Ping {
    ping
}
GQL;

    expect($query)->toBe($expected);
});

test('it can load a query from a file with includes', function () {
    $query = $this->loader->loadQuery($this->root . 'queries/singleFragmentIncludeTest.gql')->query();

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
    $query = $this->loader->loadQuery($this->root . 'queries/singleMixedFragmentFileExtensionIncludeTest.gql')->query();

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
    $query = $this->loader->loadQuery($this->root . 'queries/simplePingTest.gql')->query();

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

    $query = $this->loader->loadQuery($this->root . 'queries/variableInjectionTest.gql', $variables)->query();

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

    $query = $this->loader->loadQuery($this->root . 'queries/variableNotInArrayExceptionTest.gql', $variables)->query();

})->throws(Exception::class, 'Variable $status is set in query but not in variables array');

test('it throws an exception if fragment file is missing', function () {

    $query = $this->loader->loadQuery($this->root . 'queries/missingFragmentExceptionTest.gql')->query();

})->throws(Exception::class, 'Fragment file not found: fragments/missingFragment.gql');

test('it throws an exception if a variable is missing required keys', function () {
    $variables = [
        ['name' => 'id', 'value' => 123]
    ];

    $this->loader->loadQuery($this->root . 'queries/simplePingTest.gql', $variables);
})->throws(Exception::class, "Variable at index 0 is missing the required key 'type'.");

test('it throws an exception if name is not a string', function () {
    $variables = [
        ['name' => 123, 'type' => 'String', 'value' => 'active']
    ];

    $this->loader->loadQuery($this->root . 'queries/simplePingTest.gql', $variables);
})->throws(Exception::class, "Variable 'name' at index 0 must be a non-empty string.");

test('it throws an exception if type is not a string', function () {
    $variables = [
        ['name' => 'status', 'type' => 456, 'value' => 'active']
    ];

    $this->loader->loadQuery($this->root . 'queries/simplePingTest.gql', $variables);
})->throws(Exception::class, "Variable 'type' at index 0 must be a non-empty string.");

test('it throws an exception if value is not a scalar or null', function () {
    $variables = [
        ['name' => 'id', 'type' => 'ID!', 'value' => ['not', 'allowed']]
    ];

    $this->loader->loadQuery($this->root . 'queries/simplePingTest.gql', $variables);
})->throws(Exception::class, "Variable 'value' at index 0 must be a scalar value (string, int, float, bool, or null).");

test('it throws an exception if name is empty', function () {
    $variables = [
        ['name' => '', 'type' => 'String', 'value' => 'test']
    ];

    $this->loader->loadQuery($this->root . 'queries/simplePingTest.gql', $variables);
})->throws(Exception::class, "Variable 'name' at index 0 must be a non-empty string.");

test('it throws an exception if type is empty', function () {
    $variables = [
        ['name' => 'id', 'type' => '', 'value' => 123]
    ];

    $this->loader->loadQuery($this->root . 'queries/simplePingTest.gql', $variables);
})->throws(Exception::class, "Variable 'type' at index 0 must be a non-empty string.");
