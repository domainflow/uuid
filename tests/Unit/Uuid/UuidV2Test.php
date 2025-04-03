<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Unit\Uuid;

use DomainFlow\Uuid\UuidV2;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

#[CoversClass(UuidV2::class)]
final class UuidV2Test extends TestCase
{
    private int $uid;
    private int $gid;

    protected function setUp(): void
    {
        $this->uid = 501;
        $this->gid = 20;
    }

    /**
     * @throws RandomException
     */
    public function test_generateCreatesValidUuidV2WithUid(): void
    {
        $uuid = UuidV2::generate($this->uid, 'uid');
        $this->assertTrue(UuidV2::isValid((string) $uuid));
    }

    /**
     * @throws RandomException
     */
    public function test_generateCreatesValidUuidV2WithGid(): void
    {
        $uuid = UuidV2::generate($this->gid, 'gid');
        $this->assertTrue(UuidV2::isValid((string) $uuid));
    }

    public function test_generateThrowsExceptionOnInvalidDomain(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UuidV2::generate($this->uid, 'banana');
    }
}
