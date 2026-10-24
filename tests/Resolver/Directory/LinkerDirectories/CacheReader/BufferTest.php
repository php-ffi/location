<?php

declare(strict_types=1);

namespace FFI\Location\Tests\Resolver\Directory\LinkerDirectories\CacheReader;

use FFI\Location\Resolver\Directory\LinkerDirectories\CacheReader\Buffer;
use FFI\Location\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Buffer::class)]
final class BufferTest extends TestCase
{
    public function testOffsetOfSequenceIsFound(): void
    {
        $buffer = new Buffer('abcabc');

        self::assertSame(0, $buffer->findOffset('abc'));
        self::assertSame(2, $buffer->findOffset('ca'));
        self::assertNull($buffer->findOffset('xyz'));
    }

    public function testPrefixIsDetected(): void
    {
        $buffer = new Buffer('abcdef');

        self::assertTrue($buffer->hasPrefix('abc'));
        self::assertTrue($buffer->hasPrefix(''));
        self::assertFalse($buffer->hasPrefix('bcd'));
        self::assertFalse($buffer->hasPrefix('abcdefg'));
    }

    public function testIntegerIsReadInMachineByteOrder(): void
    {
        $buffer = new Buffer('xx' . \pack('L', 123456));

        self::assertSame(123456, $buffer->readInt(2));
    }

    public function testIntegerOutsideOfDataIsZero(): void
    {
        $buffer = new Buffer(\pack('L', 123456));

        self::assertSame(0, $buffer->readInt(-1));
        self::assertSame(0, $buffer->readInt(1));
        self::assertSame(0, $buffer->readInt(100));
    }

    public function testStringIsReadUntilNullByte(): void
    {
        $buffer = new Buffer("one\0two\0three");

        self::assertSame('one', $buffer->readString(0));
        self::assertSame('two', $buffer->readString(4));
        self::assertSame('three', $buffer->readString(8));
    }

    public function testStringOutsideOfDataIsEmpty(): void
    {
        $buffer = new Buffer('one');

        self::assertSame('', $buffer->readString(-1));
        self::assertSame('', $buffer->readString(3));
        self::assertSame('', $buffer->readString(100));
    }

    public function testReferencedStringsAreRead(): void
    {
        // Two records of 8 bytes, the reference is at offset 4, the strings start at offset 16
        $buffer = new Buffer(\pack('LL', 0, 4) . \pack('LL', 0, 0) . "foo\0bar\0");

        self::assertSame(['bar', 'foo'], \iterator_to_array($buffer->readReferences(2, 0, 8, 4, 16), false));
    }

    public function testDeclaredCountIsLimitedByDataSize(): void
    {
        $buffer = new Buffer(\pack('LL', 0, 0));

        self::assertCount(1, \iterator_to_array($buffer->readReferences(1000000, 0, 8, 4, 0), false));
    }
}
