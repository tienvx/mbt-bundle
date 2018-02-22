# MbtBundle [![Build Status](https://travis-ci.org/tienvx/mbt-bundle.svg?branch=master)](https://travis-ci.org/tienvx/mbt-bundle)

This Bundle provides ability to test your application using Model Based Testing
techique. Before testing your project, you need to create new **symfony project**
to load this bundle. Then by defining workflows (the way your system work) and
entities (the way to interact with your system) in that project, it will test
your project for you.

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require tienvx/mbt-bundle "1.0.x-dev"
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Tienvx\Bundle\MbtBundle\TienvxMbtBundle(),
        );

        // ...
    }

    // ...
}
```

### Note
If you are using symfony version 4 or later, this step is not required. Symfony do it
automatically for you.

## Resources

  * [Report issues](https://github.com/tienvx/mbt-bundle/issues)
  * [Send Pull Requests](https://github.com/tienvx/mbt-bundle/pulls)
