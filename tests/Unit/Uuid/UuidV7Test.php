<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Unit\Uuid;

use DomainFlow\Uuid\UuidV7;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

#[CoversClass(UuidV7::class)]
final class UuidV7Test extends TestCase
{
    /**
     * @throws RandomException
     */
    public function test_generatesValidUuidV7(): void
    {
        $uuid = UuidV7::generate();
        $this->assertTrue(UuidV7::isValid((string) $uuid));
    }
}
