<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Unit\Uuid;

use DomainFlow\Uuid\UuidV6;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

#[CoversClass(UuidV6::class)]
final class UuidV6Test extends TestCase
{
    /**
     * @throws RandomException
     */
    public function test_generatesValidUuidV6(): void
    {
        $uuid = UuidV6::generate();
        $this->assertTrue(UuidV6::isValid((string) $uuid));
    }

    /**
     * @throws RandomException
     */
    public function test_generatedNodeHasMulticastBitSet(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $node = substr((string) UuidV6::generate(), -12);
            $firstNodeByte = hexdec(substr($node, 0, 2));

            $this->assertSame(1, $firstNodeByte & 0x01, "Multicast bit not set on sample #$i");
        }
    }
}
