# Bin Locator

<p align="center">
    <a href="https://packagist.org/packages/ffi/location"><img src="https://poser.pugx.org/ffi/location/require/php?style=for-the-badge" alt="PHP 8.1+"></a>
    <a href="https://packagist.org/packages/ffi/location"><img src="https://poser.pugx.org/ffi/location/version?style=for-the-badge" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/ffi/location"><img src="https://poser.pugx.org/ffi/location/v/unstable?style=for-the-badge" alt="Latest Unstable Version"></a>
    <a href="https://packagist.org/packages/ffi/location"><img src="https://poser.pugx.org/ffi/location/downloads?style=for-the-badge" alt="Total Downloads"></a>
    <a href="https://raw.githubusercontent.com/php-ffi/location/master/LICENSE.md"><img src="https://poser.pugx.org/ffi/location/license?style=for-the-badge" alt="License MIT"></a>
</p>
<p align="center">
    <a href="https://github.com/php-ffi/location/actions"><img src="https://github.com/php-ffi/location/workflows/build/badge.svg"></a>
</p>

Library for searching binary files in the operating system.

## Requirements

- PHP >= 7.4

## Installation

Library is available as composer repository and can be installed using the 
following command in a root of your project.

```sh
$ composer require ffi/location
```

## Usage

### Existence Check

Checking the library for existence.

```php
use FFI\Location\Locator;

$exists = Locator::exists('libGL.so');
// Expected true in the case that the binary exists and false otherwise
```

### Binary Pathname

Getting the full path to the library.

```php
use FFI\Location\Locator;

$pathname = Locator::pathname('libGL.so');
// Expected "/usr/lib/x86_64-linux-gnu/libGL.so.1.7.0" or null
// in the case that the library cannot be found
```

### Binary Resolving

Checking multiple names to find the most suitable library.

```php
use FFI\Location\Locator;

$pathname = Locator::resolve('example.so', 'test.so', 'libvulkan.so');
// Expected "/usr/lib/x86_64-linux-gnu/libvulkan.so.1.2.131" or null
// in the case that the library cannot be found
```
