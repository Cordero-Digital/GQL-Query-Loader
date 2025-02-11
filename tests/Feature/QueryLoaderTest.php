<?php

use CorderoDigital\GQLQueryLoader\Loader;

beforeEach(function () {
    $this->root = __DIR__ . '/../';
});

// test('debug', function () {
//     $variables = [
//         'id' => 123,
//         'status' => 'active',
//     ];
//     $query = (new Loader)->loadQuery($this->root . 'queries/variableInjectionTest.gql', $variables)->query();
//     dd($query);

//     expect($query)->toBe($expected);
// });

test('it can load .gql file types', function () {
    $query = (new Loader)->loadQuery($this->root . 'queries/simplePingTest.gql')->query();

    $expected = <<<GQL
query Ping {
    ping
}
GQL;

    expect($query)->toBe($expected);
});

test('it can load .graphql file types', function () {
    $query = (new Loader)->loadQuery($this->root . 'queries/simplePingTest.graphql')->query();

    $expected = <<<GQL
query Ping {
    ping
}
GQL;

    expect($query)->toBe($expected);
});

test('it can load a query from a file with includes', function () {
    $query = (new Loader)->loadQuery($this->root . 'queries/singleFragmentIncludeTest.gql')->query();

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

test('it does not duplicate fragments', function () {
    $query = (new Loader)->loadQuery($this->root . 'queries/duplicateFragmentIncludeTest.gql')->query();

    $expected = <<<GQL
fragment SimpleFragment on Object {
    id
    name
    email
}
#include "fragments/simpleFragment.gql" - duplicate

query testSimpleSingleFragment {
    object {
        ...SimpleFragment
    }
}
GQL;

    expect($query)->toBe($expected);
});

test('it can load a query from a file with mixed file extension types', function () {
    $query = (new Loader)->loadQuery($this->root . 'queries/singleMixedFragmentFileExtensionIncludeTest.gql')->query();

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
    $query = (new Loader)->loadQuery($this->root . 'queries/simplePingTest.gql')->query();

    $expected = <<<GQL
query Ping {
    ping
}
GQL;

    expect($query)->toBe($expected);
});

test('it can dynamically inject variables from an array', function () {
    $variables = [
        'id' => 123,
        'status' => 'active',
    ];

    $expected = <<<GQL
query userQuery {
    user(id: 123, status: "active") {
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

    $query = (new Loader)->loadQuery($this->root . 'queries/variableInjectionTest.gql', $variables)->query();

    expect($query)->toBe($expected);

});

test('it throws an exception if fragment file is missing', function () {

    $query = (new Loader)->loadQuery($this->root . 'queries/missingFragmentExceptionTest.gql')->query();

})->throws(Exception::class, 'Fragment file not found: fragments/missingFragment.gql');

test('it ignores missing variables', function () {
    $variables = [
        'id' => 123,
    ];

    $query = (new Loader)->loadQuery($this->root . 'queries/variableInjectionTest.gql', $variables)->query();

    $expected = <<<GQL
query userQuery {
    user(id: 123) {
        id
        name
        email
    }
    users {
        id
        name
        email
    }
}
GQL;

    expect($query)->toBe($expected);
});
