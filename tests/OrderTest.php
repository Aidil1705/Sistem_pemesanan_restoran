<?php

declare(strict_types=1);

namespace App\Tests;

use App\Formatter;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    public function testRupiahFormatUsesIndonesianSeparator(): void
    {
        self::assertSame('Rp132.000', Formatter::rupiah(132000));
    }
}
