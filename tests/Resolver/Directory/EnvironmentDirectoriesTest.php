<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory;

use FFI\Location\Resolver\Directory\EnvironmentDirectories;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(EnvironmentDirectories::class)]
final class EnvironmentDirectoriesTest extends TestCase
{
    /**
     * @return list<string>
     */
    private function getDirectories(EnvironmentDirectories $provider): array
    {
        return \iterator_to_array($provider->getDirectories(), false);
    }

    public function testEmptyEntriesAreSkippedAndSpacesAreKept(): void
    {
        $this->setEnv('FFI_LOCATION_TEST', '/a::/b: /c :');

        self::assertSame(['/a', '/b', ' /c '], $this->getDirectories(new EnvironmentDirectories('FFI_LOCATION_TEST')));
    }

    public function testEveryDelimiterCharacterSeparatesDirectories(): void
    {
        $this->setEnv('FFI_LOCATION_TEST', '/a;/b:/c');

        self::assertSame(['/a', '/b', '/c'], $this->getDirectories(new EnvironmentDirectories('FFI_LOCATION_TEST', ':;')));
    }

    public function testUnsetOrEmptyVariableHasNoDirectories(): void
    {
        $provider = new EnvironmentDirectories('FFI_LOCATION_TEST');

        $this->setEnv('FFI_LOCATION_TEST', null);
        self::assertSame([], $this->getDirectories($provider));

        $this->setEnv('FFI_LOCATION_TEST', '');
        self::assertSame([], $this->getDirectories($provider));
    }

    public function testChangeOfVariableIsVisible(): void
    {
        $provider = new EnvironmentDirectories('FFI_LOCATION_TEST');

        $this->setEnv('FFI_LOCATION_TEST', '/a');
        self::assertSame(['/a'], $this->getDirectories($provider));

        $this->setEnv('FFI_LOCATION_TEST', '/b');
        self::assertSame(['/b'], $this->getDirectories($provider));
    }
}
