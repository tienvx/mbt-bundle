# MbtBundle

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require tienvx/mbt-bundle "1.0-dev"
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
            new tienvx\bundle\mbt-bundle(),
        );

        // ...
    }

    // ...
}
```

### Note
If you are using symfony version 4 or later, this step is not required. Symfony do it automatically for you.

## Resources

  * [Report issues](https://github.com/tienvx/mbt-bundle/issues)
  * [Send Pull Requests](https://github.com/tienvx/mbt-bundle/pulls)
