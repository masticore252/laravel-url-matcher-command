Laravel URL matcher command
===
## Motivation

This command was created to fill the need of a way to find out which route definition will match any given url and method, this is most useful in big apps with a lot of routes where debugging this can be cumbersome.

It was inspired by symfony's `bin/console router:match` command.

## Install

Require this package using composer with this command

```bash
composer require masticore/laravel-url-matcher-command
```

Laravel's auto discovery will automatically register the command into artisan console

## Usage

Given a URL the package and an http method, it will search all the registered routes for a match and show all relevant information (uri, name, handler, middlweare and others)

```bash
~$ php artisan route:match api/product/3/
+---------------------+---------------------------------------------+
| Property            | Value                                       |
+---------------------+---------------------------------------------+
| Uri                 | api/product/{product}                       |
| Prefix              | api                                         |
| Methods             | GET, HEAD                                   |
| Controller          | App\Http\Controllers\ProductController@show |
| Middleware          | api                                         |
| Namespace           | App\Http\Controllers                        |
| Parameter Names     | product                                     |
| Parameters          | 123                                         |
| Original Parameters |                                             |
| Binding Fields      |                                             |
| Is Fallback         | false                                       |
| Where               |                                             |
+---------------------+---------------------------------------------+
```

## TODO

- Unit tests
- Handle more use cases like route names, groups, subdomains, bindings
- support laravel versions older than 7.X