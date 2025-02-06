# GQL Query Loader
A simple library that adds support for `.gql` and `.graphql` files in your PHP application.

## Requirements
- PHP >=8.1

## Installation

### Install with composer
```bash
$ composer require cordero-digital/gql-query-loader
```
### Install without composer
1) Download the latest [release](https://github.com/Cordero-Digital/GQL-Query-Loader/releases)
1) Extract the `.zip` or `.tar.gz` to your machine
1) Copy `/src/Loader.php` file into your project
1) Include the `Loader.php` file into the PHP file you need the library into
```php
require_once __DIR__ . '/src/Loader.php';

use CorderoDigital\GQLQueryLoader\Loader;

$loader = new Loader;

```
## Usage/Examples

### Usage
This package supports fragments. The package assumes the fragments are located in a sub directory called `/fragments` located next to the query.

### Basic usage
This example shows how to pass in variables to your graphql file.
```graphql
# /queries/getUser.gql

query getUser{{ schemaDescription }} {
    user{{ id }} {
        id
        name
        email
    }
}
```
```php
// GetUsers.php

$loader = new Loader;

$variables = [
    [
        'name' => 'id',
        'type' => 'ID!',
        'value' => 123,
    ],
]
$query = $loader->loadQuery(__DIR__ . '/queries/getUser.gql', $variables)->query();
$query = json_encode($query, JSON_PRETTY_PRINT);
echo $query;

```
