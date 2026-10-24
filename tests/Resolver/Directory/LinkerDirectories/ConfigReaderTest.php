<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory\LinkerDirectories;

use FFI\Location\Resolver\Directory\LinkerDirectories\ConfigReader;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ConfigReader::class)]
final class ConfigReaderTest extends TestCase
{
    /**
     * @return list<string>
     */
    private function readDirectories(string $pathname): array
    {
        return \iterator_to_array(new ConfigReader($pathname), false);
    }

    public function testMissingConfigHasNoDirectories(): void
    {
        self::assertSame([], $this->readDirectories($this->createDirectory() . '/missing.conf'));
    }

    public function testCommentsAndRelativePathsAreSkipped(): void
    {
        $config = $this->createFile(
            $this->createDirectory() . '/ld.so.conf',
            "# comment\n/usr/local/lib\n\n/opt/lib # trailing comment\nrelative/ignored\n",
        );

        self::assertSame(['/usr/local/lib', '/opt/lib'], $this->readDirectories($config));
    }

    public function testSeveralDirectoriesOnOneLineAreSplit(): void
    {
        $config = $this->createFile($this->createDirectory() . '/ld.so.conf', "/a:/b,/c /d\n");

        self::assertSame(['/a', '/b', '/c', '/d'], $this->readDirectories($config));
    }

    public function testLastLineWithoutNewlineIsRead(): void
    {
        $config = $this->createFile($this->createDirectory() . '/ld.so.conf', '/last');

        self::assertSame(['/last'], $this->readDirectories($config));
    }

    public function testUnknownDirectivesAreSkipped(): void
    {
        $config = $this->createFile($this->createDirectory() . '/ld.so.conf', "hwcap 1 nosegneg\n/lib\n");

        self::assertSame(['/lib'], $this->readDirectories($config));
    }

    public function testRelativeIncludeIsResolvedAgainstIncludingConfig(): void
    {
        $directory = $this->createDirectory();

        $this->createFile($directory . '/conf.d/a.conf', "/from/a\n");
        $this->createFile($directory . '/conf.d/b.conf', "/from/b\n");
        $this->createFile($directory . '/conf.d/ignored.txt', "/ignored\n");

        $config = $this->createFile($directory . '/ld.so.conf', "include conf.d/*.conf\n/last\n");

        self::assertSame(['/from/a', '/from/b', '/last'], $this->readDirectories($config));
    }

    public function testAbsoluteIncludeIsRead(): void
    {
        $this->skipOnWindows();

        $directory = $this->createDirectory();

        $this->createFile($directory . '/conf.d/a.conf', "/from/a\n");
        $this->createFile($directory . '/other.conf', "/from/other\n");

        $config = $this->createFile(
            $directory . '/ld.so.conf',
            "include {$directory}/conf.d/*.conf\ninclude {$directory}/other.conf\n",
        );

        self::assertSame(['/from/a', '/from/other'], $this->readDirectories($config));
    }

    public function testIncludeCycleIsReadOnce(): void
    {
        $directory = $this->createDirectory();

        $first = $this->createFile($directory . '/a.conf', "/a\ninclude b.conf\n");
        $this->createFile($directory . '/b.conf', "/b\ninclude a.conf\n");

        self::assertSame(['/a', '/b'], $this->readDirectories($first));
    }
}
