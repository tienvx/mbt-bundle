# MBT Bundle [![Build Status][actions_badge]][actions_link] [![Coverage Status][coveralls_badge]][coveralls_link] [![Version][version-image]][version-url] [![PHP Version][php-version-image]][php-version-url]

This Bundle is a core library for [Sicope Model][sicope-model], a Model-Based Testing tool.

## Requirements

* PHP 8.1
* Symfony 6.1
* See also the `require` section of [composer.json](composer.json)

## Installation

```shell
composer require tienvx/mbt-bundle "^3.0"
```

## Testing

You can run the tests with:
```shell
vendor/bin/phpunit
```

## Validate code with coding standards

```shell
phpcs --standard=PSR12 src tests
php-cs-fixer fix --diff --dry-run
phpstan analyse src tests
```

## Built With

* [Symfony][symfony] - The web framework, and its components
* [Doctrine][doctrine] - Database storage and object mapping
* [A Star][a-star] - PHP A* search algorithm
* [Petrinet][petrinet] - Petrinet framework for PHP
* [Single Color Petrinet][single-color-petrinet] - Single Color Petrinet framework for PHP

## Contributing

Pull requests are welcome, please [send pull requests][pulls].

If you found any bug, please [report issues][issues].

Please read [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for details on our code of conduct, and [CONTRIBUTING.md](CONTRIBUTING.md) for the process for submitting pull requests to us.


## Authors

* **Tien Vo** - *Initial work* - [tienvx](https://tienvx.github.io/)

See also the list of [contributors][contributors] who participated in this project.

## License

This package is available under the [MIT license](LICENSE).

[actions_badge]: https://github.com/tienvx/mbt-bundle/workflows/main/badge.svg
[actions_link]: https://github.com/tienvx/mbt-bundle/actions

[coveralls_badge]: https://coveralls.io/repos/tienvx/mbt-bundle/badge.svg?branch=master&service=github
[coveralls_link]: https://coveralls.io/github/tienvx/mbt-bundle?branch=master

[version-url]: https://packagist.org/packages/tienvx/mbt-bundle
[version-image]: https://img.shields.io/github/v/release/tienvx/mbt-bundle?include_prereleases

[php-version-url]: https://packagist.org/packages/tienvx/mbt-bundle
[php-version-image]: http://img.shields.io/badge/php-8.1.0+-ff69b4.svg

[contributors]: https://github.com/tienvx/mbt-bundle/graphs/contributors
[pulls]: https://github.com/tienvx/mbt-bundle/pulls
[issues]: https://github.com/tienvx/mbt-bundle/issues

[symfony]: https://symfony.com/
[doctrine]: https://www.doctrine-project.org/
[a-star]: https://github.com/jmgq/php-a-star
[petrinet]: https://github.com/florianv/petrinet
[single-color-petrinet]: https://github.com/tienvx/single-color-petrinet
[sicope-model]: http://sicope-model.github.io/
