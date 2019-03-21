LaravelGraphQLable
=== 

## Quick start guide

Simply add the GraphQLable trait to your models or the GraphQL macro to your routes to add them to your schema. fields are automatically mapped to graphql types or can be configured to use a custom graphql field map for models and routes.

## Installation

Via Composer

``` bash
$ composer require uniben/laravelgraphqlable
```

## Usage

### Models, GraphQL Types and controllers

#### Types

LaravelGraphQLable automatically generates GraphQL Types by searching for models which use the `GraphQLQueryableTrait`

Simply add the `GraphQLQueryableTrait` trait to your model and query the graphql endpoint at `/graphql`. LaravelGraphQLable also comes shipped with graphql-playground which you can access at `/graphql-playrgound`

You may customise what properties, methods and relations your grapql model type exposes using the method `graphQLQueryable`, `graphQLMutatable` and  `graphQLRelations`.

##### Exposing fields for your graphql type

```php
public static function graphQLQueryable() {
    return [
        'id',
        'created_at',
        'updated_at'
    ];
}
```

Note: Relations you add to the graphQLRelations array must use the `GraphQLQueryableTrait`

You may also declare mutations for your model using `graphQLMutatable`. Simply override the public static method and return an array of strings which reference methods that should be included as mutations for the type.

##### Exposing type mutations 

```php
public static function graphQLMutatable(): array {
    return [
        'sayHello'
    ]
}

public function sayHello() {
    return $this->name . ' says hello!';
}
``` 

##### Exposing relations for your graphql type

```php
public static function graphQLRelations(): array {
    return [
        'page'
    ];
}

public function page() {
    $this->hasOne(Page::class);
}
```

##### Changing the default GraphQL Type name

The default name the GraphQL type uses is the pluralised version of the model name.

E.g:
```php
class User {
    use GraphQLQueryableTrait;
}
```

Generates a GraphQL Type called Users. 

An example graphql User model query may look something like this:

```
query { Users { id, name, created_at }}
```

### Controllers

You may declare special routes in your web.php file or api.php file which return a model that uses the `GraphQLQueryableTrait`

#### Adding a query to the GraphQL schema via a controller

##### The route
```php
Route::any('sayHello', 'UsersController@sayHello')->graphQL(App\User::class, 'query');
```

What's happening there?

First we define a route using the regular Laravel method.

```php
Route::get('sayHello', 'UsersController@sayHello')
```

We then use the GraphQL query macro which tells LaravelGraphQLable to add this controller method (or closure) to our GraphQLSchema

```php
...->graphQL(App\User::class, 'query');
```

For the first parameter we provide the class path of the GraphQL Type we wish to return. In the example above we reference a Laravel model which uses the `GraphQLQueryableTrait`

We do not need to retrieve the type for the model. As long as it uses the trait, it is done automatically.

For the second parameter, we specify if the type should be a query or mutation.

##### Overriding a controller type name

A route which uses the graphQL macro generates a graphQL type which has a default name of {controller}{Method}{Type} (If your controller name has the word Controller as the suffix it is removed)

If you want to override the default name generated for your type, simply add the name macro to your route.

```php
->graphQL(...)->name('SayHello');
```

## Change log

Please see the [changelog](changelog.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email ben@blwatson.com instead of using the issue tracker.

## Credits

- [Ben Watson][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/uniben/laravelgraphqlable.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/uniben/laravelgraphqlable.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/uniben/laravelgraphqlable/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/uniben/laravelgraphqlable
[link-downloads]: https://packagist.org/packages/uniben/laravelgraphqlable
[link-travis]: https://travis-ci.org/uniben/laravelgraphqlable
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/uniben
[link-contributors]: ../../contributors]
