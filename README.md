PerbilityBCryptBundle  [![Build Status](https://travis-ci.org/PERBILITY/BCryptBundle.png)](https://travis-ci.org/PERBILITY/BCryptBundle)
=====================
This bundle provides an easy way to create and work with BCrypt-hashes in a Symfony2-context.


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

Additionally you can set the bcrypt cost-factor (default is 12) with `cost_factor`. A complete configuration can therefore look like this:

``` yml
perbility_bcrypt:
  global_salt: please-change-me-to-something-secret
  cost_factor: 14
```

In early versions of the bundle `cost_factor` was named `iterations`. The old configuration key is still read (as long as there is no `cost_factor` present), but deprecated.


Usage
-----
The BCrypt can be used either as a service from the service-container or as a command-line tool.

### Service-Container
The BCrypt-service is registered by default as `perbility_bcrypt`. The service is an instance of `Perbility\Bundle\BCryptBundle\BCrypt\BCrypt` and will behave accordingly.

### Command-Line
- `bcrypt:hash` 
- `bcrypt:check`
- `bcrypt:benchmark`


License
-------
The library is licensed under the MIT license. For the full license text, see `Resource/meta/LICENSE`


TODO
----
- Refine requirements (add Symfony 2.0, when tested)
- Improve documentation
