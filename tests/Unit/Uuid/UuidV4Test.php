<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Unit\Uuid;

use DomainFlow\Uuid\UuidV4;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

#[CoversClass(UuidV4::class)]
final class UuidV4Test extends TestCase
{
    /**
     * @throws RandomException
     */
    public function test_generatesValidUuidV4(): void
    {
        $uuid = UuidV4::generate();
        $this->assertTrue(UuidV4::isValid((string) $uuid));
    }
}
