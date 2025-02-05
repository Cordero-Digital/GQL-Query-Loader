<?php

use CorderoDigital\QueryLoader\Loader;

beforeEach(function () {
    $this->root = __DIR__ . '/../../';
});

test('it can load a query from a file', function () {
    $query = Loader::load($this->root . 'queries/ping.gql');

    $expected = <<<GQL
query Ping {
    ping
}
GQL;

    expect($query)->toBe($expected);
});

test('it can load a query from a file with includes', function () {
    $query = Loader::load($this->root . 'queries/getUser.gql');

    $expected = <<<GQL
fragment UserFragment on User {
    id
    name
    email
}

query getUser(\$id: ID!) {
    user(id: \$id) {
        ...UserFragment
    }
}
GQL;

    expect($query)->toBe($expected);
});
