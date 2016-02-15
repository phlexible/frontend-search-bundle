PhlexibleFrontendSearchBundle
=============================

The PhlexibleFrontendSearchBundle adds a generic search front end for elements that are index through the PhlexibleIndexerElementBundle.

Installation
------------

Installation is a 4 step process:

1. Download PhlexibleFrontendSearchBundle using composer
2. Enable the Bundle
3. Import PhlexibleFrontendSearchBundle routing
4. Clear the symfony cache

### Step 1: Download PhlexibleFrontendSearchBundle using composer

Add PhlexibleFrontendSearchBundle by running the command:

``` bash
$ php composer.phar require phlexible/element-finder-bundle "~1.0.0"
```

Composer will install the bundle to your project's `vendor/phlexible` directory.

### Step 2: Enable the bundle

Enable the bundle and the required WhiteOctoberPagerfantaBundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Phlexible\Bundle\FrontendSearchBundle\PhlexibleFrontendSearchBundle(),
        new WhiteOctober\PagerfantaBundle\WhiteOctoberPagerfantaBundle(),
    );
}
```

### Step 3: Import PhlexibleFrontendSearchBundle routing

Import the PhlexibleFrontendSearchBundle routing.

``` yaml
# app/config/routing.yml
phlexible_frontendsearch_search:
    resource: "@PhlexibleFrontendSearchBundle/Controller/SearchController.php"
    type:     annotation
```

### Step 4: Clear the symfony cache

If you access your phlexible application with environment prod, clear the cache:

``` bash
$ php app/console cache:clear --env=prod
```
