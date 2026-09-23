<?php

namespace Tests\Unit;

use App\Services\TransportPricingService;
use PHPUnit\Framework\TestCase;

class TransportPricingServiceTest extends TestCase
{
    public function test_two_way_price_uses_both_directional_rates_and_discount(): void
    {
        $pricing = new TransportPricingService();

        $this->assertSame(1800.0, $pricing->calculateTwoWay(1000, 1000, 10)['final_price']);
        $this->assertSame(1980.0, $pricing->calculateTwoWay(1000, 1200, 10)['final_price']);
        $this->assertSame(1785.0, $pricing->calculateTwoWay(1200, 900, 15)['final_price']);
        $this->assertSame(2200.0, $pricing->calculateTwoWay(1000, 1200, 0)['final_price']);
        $this->assertSame(0.0, $pricing->calculateTwoWay(1000, 1200, 100)['final_price']);
    }

    public function test_one_way_uses_the_selected_directional_rate(): void
    {
        $pricing = new TransportPricingService();

        $this->assertSame(1000.0, $pricing->calculateOneWay(1000, 1200, false)['final_price']);
        $this->assertSame(1200.0, $pricing->calculateOneWay(1000, 1200, true)['final_price']);
    }
}
