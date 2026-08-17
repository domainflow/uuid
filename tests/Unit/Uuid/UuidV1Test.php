<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Unit\Uuid;

use DomainFlow\Uuid\UuidV1;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

#[CoversClass(UuidV1::class)]
final class UuidV1Test extends TestCase
{
    /**
     * @throws RandomException
     */
    public function test_generatesValidUuidV1(): void
    {
        $uuid = UuidV1::generate();
        $this->assertTrue(UuidV1::isValid((string) $uuid));
    }

    /**
     * @throws RandomException
     */
    public function test_generatedNodeHasMulticastBitSet(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $node = substr((string) UuidV1::generate(), -12);
            $firstNodeByte = hexdec(substr($node, 0, 2));

            $this->assertSame(1, $firstNodeByte & 0x01, "Multicast bit not set on sample #$i");
        }
    }
}
