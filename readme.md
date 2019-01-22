LaravelGraphqlable
=== 

## Quick start guide

It's really easy to make your models queryable via  a GraphQL endpoint. The point of this package is to allow quick communication with an API without having to much other than define your models and extend the GraphQLModel.

1. Modify your model to extend `UniBen\LaravelGraphQLable\Models\GraphQLModel`,
2. Go to the GraphQL Playground (/graphql-playground) and start typing in the name of your model inside an empty Json object.
3. Press the play button and see your data return. Tada!

## Installation

Via Composer

``` bash
$ composer require uniben/laravelgraphqlable
```

## Usage

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
