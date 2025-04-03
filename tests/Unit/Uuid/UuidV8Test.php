<?php

declare(strict_types=1);

namespace DomainFlow\Uuid\Tests\Unit\Uuid;

use DomainFlow\Uuid\UuidV8;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

#[CoversClass(UuidV8::class)]
final class UuidV8Test extends TestCase
{
    /**
     * @throws RandomException
     */
    public function test_generatesValidUuidV8(): void
    {
        $uuid = UuidV8::generate();
        $this->assertTrue(UuidV8::isValid((string) $uuid));
    }

}
