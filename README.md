# MBT Bundle [![Build Status][travis_badge]][travis_link] [![Coverage Status][coveralls_badge]][coveralls_link]

This Bundle provides ability to test your application using Model Based Testing
techique.

The major features are:
1. Allow to test your application even though the application is not implemented yet.
2. Automatically generate test cases when a task is created.
3. Automatically execute those test cases to test your application.
4. Automatically reduce reproduce path when a bug is found.
5. Automatically report the bug when the reproduce path is reduced.

All you have to do:
1. Define models to describe your application.
2. Define subjects to interact with your application.
3. Create tasks based on your need. e.g.:
    1. Test the whole application to make sure there are no bugs in the application.
    2. Test only models that have a tag to make sure the part of your application is still working while developing a feature.
    3. Test a model to make sure a bug that has been fixed is not regressed.
4. Manage bugs that has been found. e.g. mark a bug has been fixed.
5. You don't have to maintains test cases that has been generated automatically, instead, you may need
   to maintains models and subjects.

## Requirements

* PHP 7.1 / 7.2 / 7.3
* Symfony 4.2
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
$ composer require tienvx/mbt-bundle "^1.6"
```

### Step 3: Create models and subjects

Model is the way to describe part your application. Subject is
the way to tell this bundle to interact with your application.

## Testing

You can run the tests with:
```console
$ phpunit
$ composer run-script test # if you want to clear the cache
```

## Documentation

For the usage guide and reference, see [wiki][wiki]

## Built With

* [Symfony][symfony] - The web framework, and its components
* [Graphp algorithms][graphp] - Common mathematical graph algorithms implemented in PHP
* [Doctrine][doctrine] - Database storage and object mapping

## Contributing

Pull requests are welcome, please [send pull requests][pulls].

If you found any bug, please [report issues][issues].

Please read [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md) for details on our code of conduct, and [CONTRIBUTING.md](CONTRIBUTING.md) for the process for submitting pull requests to us.


## Authors

* **Tien Vo** - *Initial work* - [tienvx](https://tienvx.github.io/)

See also the list of [contributors][contributors] who participated in this project.

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

[symfony]: https://symfony.com/
[graphp]: https://github.com/graphp/algorithms
[doctrine]: https://www.doctrine-project.org/
