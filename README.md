[![Version](http://img.shields.io/packagist/v/phpcq/travis-configuration-check.svg?style=flat-square)](https://packagist.org/packages/phpcq/travis-configuration-check)
[![Stable Build Status](http://img.shields.io/travis/phpcq/travis-configuration-check/master.svg?style=flat-square)](https://travis-ci.org/phpcq/travis-configuration-check)
[![Upstream Build Status](http://img.shields.io/travis/phpcq/travis-configuration-check/develop.svg?style=flat-square)](https://travis-ci.org/phpcq/travis-configuration-check)
[![License](http://img.shields.io/packagist/l/phpcq/travis-configuration-check.svg?style=flat-square)](https://github.com/phpcq/travis-configuration-check/blob/master/LICENSE)
[![Downloads](http://img.shields.io/packagist/dt/phpcq/travis-configuration-check.svg?style=flat-square)](https://packagist.org/packages/phpcq/travis-configuration-check)

Validate .travis.yml against composer.json.
============================================

This check performs several tests.

The first test ensures that a PHP version is set in the composer.json and this version is also covered by the .travis.yml.

The second test is to ensure that all allowed versions in .travis.yml are available at 
[travis-ci](http://docs.travis-ci.com/user/ci-environment/#PHP-versions). 

Optionally it tests, that no unmaintained PHP version is set in the composer.json and .travis.yml and errors
when an unmaintained PHP version is mentioned. Currently this means any version prior to PHP 5.4.

Usage
-----

Add to your `composer.json` in the `require-dev` section:
```
"phpcq/travis-configuration-check": "~1.0"
```

Call the binary:
```
./vendor/bin/check-travis-configuration.php
```

Optionally pass the root of the project to check:
```
./vendor/bin/check-travis-configuration.php /path/to/some/project
```

To additionally check for unmaintained PHP versions:
```
./vendor/bin/check-travis-configuration.php --unmaintained-version-error
```

To additionally check for unmaintained PHP versions within another project:
```
./vendor/bin/check-travis-configuration.php --unmaintained-version-error /path/to/some/project
```
