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
}
