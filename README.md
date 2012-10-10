PerbilityBCryptBundle
=====================

This bundle provides an easy way to create and work with BCrypt-hashes in a Symfony2-context.

[![Build Status](https://travis-ci.org/PERBILITY/BCryptBundle.png)](https://travis-ci.org/PERBILITY/BCryptBundle)

Requirements
------------

- PHP >= 5.3
- Symfony2 >= 2.1

In case you want to use the interactive password/value prompt in the command-line mode, your system should be able to execute `/usr/bin/env bash`. PHP does not support "hidden" input from STDIN on its own.

Installation
------------

It's recommended to install this bundle (and Symfony2) with [composer](http://getcomposer.org). To install you need to run the following command:

``` bash
$ composer require perbility/bcrypt-bundle
```

Then update your AppKernel.php file, and register the new bundle:

``` php

// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Perbility\Bundle\BCryptBundle\PerbilityBCryptBundle()
    // ...
);

```

Finally you have to set up one configuration-parameter (see paragraph below) und you're done.


Configuration
-------------

The BCryptBundle requires the presence of a "global_salt". This can be done in the app's configuration, e.g. config.yml:

``` yml
perbility_bcrypt:
  global_salt: please-change-me-to-something-secret
```

Additionally you can set the number of a bcrypt-iterations (default is 12) with `iterations`. A complete configuration can therefore look like this:

``` yml
perbility_bcrypt:
  global_salt: please-change-me-to-something-secret
  iterations: 14
```


Usage
-----

The BCrypt can be used either as a service from the service-container or as a command-line tool.

### Service-Container

The BCrypt-service is registered by default as `perbility_bcrypt`. The service is an instance of `Perbility\Bundle\BCryptBundle\BCrypt\BCrypt` and will behave accordingly.

### Command-Line

@todo


License
-------

The library is licensed under the MIT license. See the license text in `LICENSE.txt`


TODO
----
- Refine requirements
- Write tests
- Improve documentation
