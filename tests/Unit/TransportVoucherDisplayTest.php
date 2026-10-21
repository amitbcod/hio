<?php

namespace Tests\Unit;

use App\Support\TransportVoucherDisplay;
use PHPUnit\Framework\TestCase;

class TransportVoucherDisplayTest extends TestCase
{
    public function test_route_label_uses_pdf_compatible_separator(): void
    {
        $this->assertSame('Airport - North', TransportVoucherDisplay::routeLabel('Airport', 'North'));
    }

    public function test_route_label_omits_missing_endpoint(): void
    {
        $this->assertSame('Airport', TransportVoucherDisplay::routeLabel('Airport', null));
        $this->assertSame('North', TransportVoucherDisplay::routeLabel(null, ' North '));
    }

    public function test_route_label_falls_back_when_both_endpoints_are_missing(): void
    {
        $this->assertSame('N/A', TransportVoucherDisplay::routeLabel(null, ' '));
    }
}
