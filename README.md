# MBT Bundle [![Build Status][travis_badge]][travis_link] [![Coverage Status][coveralls_badge]][coveralls_link]

This Bundle provides ability to test your application using Model Based Testing
techique. The major features are: automatically test your application when a task is created,
automatically reduce reproduce path when a bug is found, automatically report the bug when the
reproduce path is reduced.

## Requirements

* PHP 7.1 / 7.2
* Symfony 4.1
* See also the `require` section of [composer.json](composer.json)

## Installation

### Step 1: Create symfony project

Before testing your application, you need to create new **symfony project**
to use this bundle:

```console
$ composer create-project symfony/skeleton my-project
```

### Step 2: Download the Bundle

Install lastest version of this bundle:

```console
$ composer require tienvx/mbt-bundle "1.0.x-dev"
```

### Step 3: Create models and subjects

Model is the way to describe part your application. Subject is
the way to tell this bundle to interact with your application.

## Documentation

For the usage guide and reference, see [wiki][wiki]

## Contributing

Pull requests are welcome, please [send pull requests][pulls].

If you found any bug, please [report issues][issues].

Thanks to
[everyone who has contributed][contributors] already.

## License

This package is available under the [MIT license](LICENSE).

[travis_badge]: https://travis-ci.org/tienvx/mbt-bundle.svg?branch=master
[travis_link]: https://travis-ci.org/tienvx/mbt-bundle

[coveralls_badge]: https://coveralls.io/repos/tienvx/mbt-bundle/badge.svg?branch=master&service=github
[coveralls_link]: https://coveralls.io/github/tienvx/mbt-bundle?branch=master

[wiki]: https://github.com/tienvx/mbt-bundle/wiki
[contributors]: https://github.com/tienvx/mbt-bundle/graphs/contributors
[pulls]: https://github.com/tienvx/mbt-bundle/pulls
[issues]: https://github.com/tienvx/mbt-bundle/issues
