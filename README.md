
# GQL Query Loader
A simple library that adds support for `.gql` and `.graphql` files in your PHP application.

## Features
- Load GraphQL queries from `.graphql` and `.gql` files.
- Supports variable replacement within queries.
- Includes fragment files via `#include "fragment.graphql"` syntax.
- Prevents duplicate fragment loading.

## Requirements
- PHP >=8.1

## Installation
### Install with composer
```bash
$  composer  require  cordero-digital/gql-query-loader
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
## Usage
This package supports fragments. The package assumes the fragments are located in a sub directory called `/fragments` located next to the query.

### Variable replacement
This package supports passing PHP values into Graphql files by doing string replacement. This allows you to hardcode your PHP variables into the compiled Graphql query. A couple things to note:

You pass the variables in to the `loadQuery()` method where the key is the variable name in the Graphql file. Ex:
```php
// Your PHP file
$query = (new  Loader)->loadQuery($this->root  .  'queries/variableNotInArrayExceptionTest.gql', ['id' => 123])->query();
```
```graphql
# Your graphql file
query {
	user{{ id }} {
		id
		name
	}
}
```
```graphql
# result
query {
	user(id: 123) {
		id
		name
	}
}
```
If you want multiple hard coded variables on a single object you would do:
```php
// your PHP file
$query = (new  Loader)->loadQuery($this->root  .  'queries/variableNotInArrayExceptionTest.gql', ['id' => 123, 'status' => 'active'])->query();
```
```graphql
# Your graphql file
query {
	user{{ id, status }} {
		id
		name
	}
}
```
```graphql
# result
query {
	user(id: 123, status: "active") {
		id
		name
	}
}
```
