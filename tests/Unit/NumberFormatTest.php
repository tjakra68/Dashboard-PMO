<?php

namespace Tests\Unit;

use App\Support\NumberFormat;
use PHPUnit\Framework\TestCase;

class NumberFormatTest extends TestCase
{
    public function test_it_keeps_billions_as_the_largest_unit(): void
    {
        $this->assertSame('1,375.77 B', NumberFormat::compact(1_375_770_000_000));
        $this->assertSame('420.13 B', NumberFormat::compact(420_130_000_000));
    }

    public function test_it_scales_smaller_amounts(): void
    {
        $this->assertSame('12.50 M', NumberFormat::compact(12_500_000));
        $this->assertSame('1.20 K', NumberFormat::compact(1_200));
        $this->assertSame('-950.00', NumberFormat::compact(-950));
    }

    public function test_it_supports_a_custom_suffix_separator(): void
    {
        $this->assertSame('1,375.77B', NumberFormat::compact(1_375_770_000_000, ''));
    }
}
