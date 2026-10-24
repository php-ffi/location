<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory;

use FFI\Location\Resolver\Directory\Environment;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Environment::class)]
final class EnvironmentTest extends TestCase
{
    public function testValueOfVariableIsFound(): void
    {
        $this->setEnv('FFI_LOCATION_TEST', 'value');

        self::assertSame('value', Environment::findValue('FFI_LOCATION_TEST'));
    }

    public function testUnsetVariableIsNotFound(): void
    {
        $this->setEnv('FFI_LOCATION_TEST', null);

        self::assertNull(Environment::findValue('FFI_LOCATION_TEST'));
    }

    public function testEmptyVariableIsNotFound(): void
    {
        $this->setEnv('FFI_LOCATION_TEST', '');

        self::assertNull(Environment::findValue('FFI_LOCATION_TEST'));
    }

    public function testChangeOfVariableIsVisible(): void
    {
        $this->setEnv('FFI_LOCATION_TEST', 'first');
        self::assertSame('first', Environment::findValue('FFI_LOCATION_TEST'));

        $this->setEnv('FFI_LOCATION_TEST', 'second');
        self::assertSame('second', Environment::findValue('FFI_LOCATION_TEST'));
    }
}
