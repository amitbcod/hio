<?php

namespace Tests\Unit;

use App\Models\Transport;
use App\Models\TransportRoute;
use App\Services\GroupTransportRouteResolver;
use PHPUnit\Framework\TestCase;

class GroupTransportRouteResolverTest extends TestCase
{
    public function test_it_keeps_selected_route_directions_and_days_distinct(): void
    {
        $transport = new Transport();
        $transport->setRelation('routes', collect([
            new TransportRoute([
                'id' => 377,
                'route_id' => 'airport-south-east',
                'route_from' => 'Airport',
                'route_to' => 'South East',
            ]),
        ]));
        $resolver = new GroupTransportRouteResolver();

        $dayOne = $resolver->selectedLegs($transport, [
            'transport_schedule' => [
                'airport_transfer' => [
                    'airport-south-east-fwd' => ['selected' => '1'],
                    'airport-south-east-rev' => ['selected' => '1'],
                ],
            ],
        ]);
        $dayTwo = $resolver->selectedLegs($transport, [
            'transport_schedule' => [
                'airport_transfer' => [
                    'airport-south-east-fwd' => ['selected' => '1'],
                ],
            ],
        ]);

        $this->assertSame([
            ['route_id' => 'airport-south-east', 'route_from' => 'Airport', 'route_to' => 'South East', 'direction' => 'forward'],
            ['route_id' => 'airport-south-east', 'route_from' => 'South East', 'route_to' => 'Airport', 'direction' => 'reverse'],
        ], $dayOne);
        $this->assertSame('Airport → South East', $resolver->label($transport, [
            'transport_schedule' => ['airport_transfer' => ['airport-south-east-fwd' => ['selected' => '1']]],
        ]));
        $this->assertSame('South East', $dayTwo[0]['route_from']);
        $this->assertSame('Airport', $dayTwo[0]['route_to']);
    }

    public function test_explicit_unresolved_selection_does_not_fall_back_to_another_transport_route(): void
    {
        $transport = new Transport();
        $transport->route_from = 'Airport';
        $transport->route_to = 'West';
        $transport->setRelation('routes', collect([
            new TransportRoute([
                'id' => 376,
                'route_id' => 'airport-west',
                'route_from' => 'Airport',
                'route_to' => 'West',
            ]),
        ]));
        $resolver = new GroupTransportRouteResolver();
        $dayEntry = [
            'transport_schedule' => [
                'airport_transfer' => [
                    'airport-south-east-fwd' => ['selected' => '1'],
                ],
            ],
        ];

        $this->assertTrue($resolver->hasExplicitSelection($dayEntry));
        $this->assertSame([], $resolver->selectedLegs($transport, $dayEntry));
        $this->assertSame('', $resolver->label($transport, $dayEntry));
    }
}