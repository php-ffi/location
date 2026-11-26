# Library Location Resolver

<p align="center">
    <a href="https://packagist.org/packages/ffi/location"><img src="https://poser.pugx.org/ffi/location/require/php?style=for-the-badge" alt="PHP 8.1+"></a>
    <a href="https://packagist.org/packages/ffi/location"><img src="https://poser.pugx.org/ffi/location/version?style=for-the-badge" alt="Latest Stable Version"></a>
    <a href="https://packagist.org/packages/ffi/location"><img src="https://poser.pugx.org/ffi/location/v/unstable?style=for-the-badge" alt="Latest Unstable Version"></a>
    <a href="https://packagist.org/packages/ffi/location"><img src="https://poser.pugx.org/ffi/location/downloads?style=for-the-badge" alt="Total Downloads"></a>
    <a href="https://raw.githubusercontent.com/php-ffi/location/master/LICENSE.md"><img src="https://poser.pugx.org/ffi/location/license?style=for-the-badge" alt="License MIT"></a>
</p>
<p align="center">
    <a href="https://github.com/php-ffi/location/actions"><img src="https://github.com/php-ffi/location/workflows/Tests/badge.svg"></a>
</p>

`FFI::cdef()` fails with a cryptic error when a shared library is missing, and the
same library is named differently on each operating system (`libvulkan.so.1`,
`vulkan-1.dll`, `libvulkan.1.dylib`). This package finds the library before you
load it, the way the dynamic linker of the operating system searches it, so you
can check that it is installed, pick the name that exists and report a clear error.

## Installation

```sh
composer require ffi/location
```

Requires PHP 8.1 or later.

## Usage

```php
use FFI\Location\Locator;

$pathname = Locator::resolve('libvulkan.so.1', 'vulkan-1.dll', 'libvulkan.1.dylib')
    ?? throw new RuntimeException('Vulkan is not installed');

$vulkan = FFI::cdef($header, $pathname);
```

## Documentation

The search order on each system, the options and the limitations are described
in the [documentation](https://php-ffi.github.io/utils/location/).
