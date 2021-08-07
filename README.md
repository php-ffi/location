# Bin Locator

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
