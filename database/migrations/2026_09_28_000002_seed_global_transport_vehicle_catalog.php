<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $catalog = [
            'Economy Sedan' => [
                ['Toyota Camry', 4],
                ['Honda Accord', 4],
                ['Nissan Altima', 4],
                ['Hyundai Sonata', 4],
                ['Kia K5', 4],
                ['Volkswagen Passat', 4],
                ['Skoda Superb', 4],
                ['Ford Mondeo / Fusion', 4],
                ['Chevrolet Malibu', 4],
            ],
            'Executive Sedan' => [
                ['Mercedes E-Class', 3],
                ['BMW 5 Series', 3],
                ['Audi A6', 3],
                ['Volvo S90', 3],
                ['Lexus ES', 3],
                ['Genesis G80', 3],
                ['Cadillac CT5', 3],
            ],
            'VIP Luxury Sedan' => [
                ['Mercedes S-Class', 3],
                ['BMW X Series', 3],
                ['Audi A8', 3],
                ['Lexus LS', 3],
                ['Genesis G90', 3],
                ['Cadillac CT6', 3],
                ['Bentley Flying Spur', 3],
                ['Rolls-Royce Ghost', 3],
            ],
            'SUV (Standard)' => [
                ['Ford Escape / Kuga', 4],
                ['Chevrolet Equinox', 4],
                ['Volkswagen Tiguan', 4],
                ['Toyota RAV4', 4],
                ['Honda CR-V', 4],
                ['Nissan X-Trail / Rogue', 4],
                ['Hyundai Tucson', 4],
                ['Kia Sportage', 4],
            ],
            'SUV (Full-Size)' => [
                ['Toyota Sequoia', 7],
                ['Toyota Land Cruiser', 7],
                ['Nissan Patrol / Armada', 7],
                ['BMW X7', 6],
                ['Mercedes GLS', 6],
                ['Range Rover LWB', 5],
                ['Cadillac Escalade', 7],
                ['Chevrolet Suburban', 7],
                ['GMC Yukon XL', 7],
                ['Lincoln Navigator', 7],
            ],
            'Minivan (MPV)' => [
                ['Chrysler Pacifica', 7],
                ['Toyota Alphard / Vellfire', 7],
                ['Toyota Sienna', 7],
                ['Honda Odyssey', 7],
                ['Kia Carnival', 8],
                ['Hyundai Staria', 9],
                ['Mercedes V-Class', 7],
                ['Volkswagen Multivan', 7],
            ],
            'Minibus' => [
                ['Ford Transit Wagon', 15],
                ['Chevrolet Express', 15],
                ['Toyota HiAce', 15],
                ['Nissan NV350 Urvan', 15],
                ['Hyundai H350', 15],
                ['Mercedes Sprinter Tourer', 19],
                ['Volkswagen Crafter', 19],
                ['Renault Master', 17],
                ['Iveco Daily Minibus', 19],
            ],
            'Van (Executive)' => [
                ['Mercedes Sprinter Custom VIP', 14],
                ['Ford Transit Custom Luxury', 10],
            ],
            'Coaster (Midi)' => [
                ['Toyota Coaster', 30],
                ['Mitsubishi Fuso Rosa', 30],
                ['Isuzu Journey', 30],
                ['Hyundai County', 30],
                ['Higer Midi Bus', 30],
            ],
            'Coach' => [
                ['MCI J4500', 60],
                ['Prevost H3-45', 56],
                ['Volvo 9000 Series', 60],
                ['Scania Tourliner', 60],
                ['Setra ComfortClass', 60],
                ['Neoplan Tourliner', 60],
                ['Van Hool TX Series', 60],
                ['Yutong Bus', 60],
            ],
            'Double-Decker Bus' => [
                ['Wrightbus Eclipse', 80],
                ['Alexander Dennis Enviro', 85],
                ['Ayats Bravo City', 85],
                ['Ankai Double Decker', 80],
            ],
            '4x4 Safari' => [
                ['Land Rover Defender', 9],
                ['Toyota Land Cruiser Safari', 9],
                ['Nissan Patrol Safari', 9],
                ['Ford Bronco Safari', 6],
            ],
        ];

        $now = now();

        DB::transaction(function () use ($catalog, $now): void {
            foreach ($catalog as $typeName => $vehicles) {
                DB::table('transport_vehicle_types')->updateOrInsert(
                    ['name' => $typeName],
                    [
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                $vehicleTypeId = DB::table('transport_vehicle_types')
                    ->where('name', $typeName)
                    ->value('id');

                foreach ($vehicles as [$vehicleName, $seatCapacity]) {
                    DB::table('transport_vehicle_names')->updateOrInsert(
                        [
                            'name' => $vehicleName,
                            'transport_vehicle_type_id' => $vehicleTypeId,
                        ],
                        [
                            'seat_capacity' => $seatCapacity,
                            'is_active' => true,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                }
            }
        });
    }

    public function down(): void
    {
        // Keep catalog records on rollback because transports may reference them.
    }
};