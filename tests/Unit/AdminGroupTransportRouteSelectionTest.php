<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\ClosedGroupBookingController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class AdminGroupTransportRouteSelectionTest extends TestCase
{
    public function test_it_preserves_forward_and_reverse_group_route_legs(): void
    {
        $controller = new ClosedGroupBookingController();
        $method = new ReflectionMethod($controller, 'extractSelectedRouteGroups');
        $method->setAccessible(true);

        $groups = $method->invoke($controller, [
            'transport_schedule' => [
                'airport_transfer' => [
                    'TRN-13-airport-transfer-airport-south-east-fwd' => [
                        'selected' => '1',
                        'start_hour' => '10',
                        'start_min' => '00',
                    ],
                    'TRN-13-airport-transfer-airport-south-east-rev' => [
                        'selected' => '1',
                        'start_hour' => '11',
                        'start_min' => '30',
                    ],
                ],
            ],
        ]);

        $this->assertCount(1, $groups);
        $this->assertSame(['trn-13-airport-transfer-airport-south-east'], $groups[0]['route_ids']);
        $this->assertCount(2, $groups[0]['legs']);
        $this->assertSame('forward', $groups[0]['legs'][0]['direction']);
        $this->assertSame('10:00:00', $groups[0]['legs'][0]['pickup_time']);
        $this->assertSame('reverse', $groups[0]['legs'][1]['direction']);
        $this->assertSame('11:30:00', $groups[0]['legs'][1]['pickup_time']);
        $this->assertTrue($groups[0]['legs'][0]['is_return_pair']);
        $this->assertTrue($groups[0]['legs'][1]['is_return_pair']);
    }
}