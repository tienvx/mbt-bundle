# MBT Bundle [![Build Status][actions_badge]][actions_link] [![Coverage Status][codecov_badge]][codecov_link]

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

* PHP 7.2 / 7.3
* Symfony 4.3
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
$ composer require tienvx/mbt-bundle "^1.13"
```

### Step 3: Config file storage

In order to save screenshots of bug report, we need to configure file system:
```yaml
flysystem:
    storages:
        # Name of the storage is matter
        mbt.storage:
            adapter: 'local'
            options:
                directory: '%kernel.project_dir%/var/storage/screenshots'
```

### Step 4: Create models and subjects

Model is the way to describe part your application. Subject is
the way to tell this bundle to interact with your application.

## Testing

You can run the tests with:
```console
$ php tests/app/bin/console cache:clear # Only if you added new code
$ phpunit
```

## Validate code with coding standards

```console
$ php-cs-fixer fix --diff --dry-run
```

## Documentation

For the usage guide and reference, see [docs][docs]

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

[actions_badge]: https://github.com/tienvx/mbt-bundle/workflows/main/badge.svg
[actions_link]: https://github.com/tienvx/mbt-bundle/actions

[codecov_badge]: https://codecov.io/gh/tienvx/mbt-bundle/branch/master/graph/badge.svg
[codecov_link]: https://codecov.io/gh/tienvx/mbt-bundle

[docs]: https://mbtbundle.gitbook.io/docs/
[contributors]: https://github.com/tienvx/mbt-bundle/graphs/contributors
[pulls]: https://github.com/tienvx/mbt-bundle/pulls
[issues]: https://github.com/tienvx/mbt-bundle/issues

[symfony]: https://symfony.com/
[graphp]: https://github.com/graphp/algorithms
[doctrine]: https://www.doctrine-project.org/
