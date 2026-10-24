<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory;

use FFI\Location\Resolver\Directory\LinkerDirectories;
use FFI\Location\Resolver\Directory\LinkerDirectories\ReaderInterface;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LinkerDirectories::class)]
final class LinkerDirectoriesTest extends TestCase
{
    /**
     * @param list<string> $directories
     */
    private function createReader(array $directories, int &$reads): ReaderInterface
    {
        return new class ($directories, $reads) implements ReaderInterface {
            /**
             * @var list<string>
             */
            private array $directories;

            private int $reads;

            /**
             * @param list<string> $directories
             */
            public function __construct(array $directories, int &$reads)
            {
                $this->directories = $directories;
                $this->reads = &$reads;
            }

            public function getIterator(): \Traversable
            {
                ++$this->reads;

                return new \ArrayIterator($this->directories);
            }
        };
    }

    public function testDirectoriesOfReadersAreReturnedInOrder(): void
    {
        $reads = [0, 0];

        $provider = new LinkerDirectories(
            $this->createReader(['/first', '/second'], $reads[0]),
            $this->createReader(['/third'], $reads[1]),
        );

        self::assertSame(['/first', '/second', '/third'], \iterator_to_array($provider->getDirectories(), false));
    }

    public function testLaterReaderIsNotReadUntilItIsNeeded(): void
    {
        $reads = [0, 0];

        $provider = new LinkerDirectories(
            $this->createReader(['/first'], $reads[0]),
            $this->createReader(['/second'], $reads[1]),
        );

        foreach ($provider->getDirectories() as $directory) {
            self::assertSame('/first', $directory);

            break;
        }

        self::assertSame([1, 0], $reads);
    }

    public function testEachReaderIsReadOnce(): void
    {
        $reads = [0];

        $provider = new LinkerDirectories($this->createReader(['/first'], $reads[0]));

        \iterator_to_array($provider->getDirectories(), false);
        \iterator_to_array($provider->getDirectories(), false);

        self::assertSame([1], $reads);
    }

    public function testNoReadersHaveNoDirectories(): void
    {
        self::assertSame([], \iterator_to_array((new LinkerDirectories())->getDirectories(), false));
    }
}
