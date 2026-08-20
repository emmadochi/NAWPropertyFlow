<?php

namespace Database\Seeders;

use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $properties = [
            [
                'name' => 'Hutu Prestige - 3 Bedroom Terrace Duplex (150 SQM)',
                'estate_name' => 'Hutu Prestige Polo Lake Resort',
                'location' => 'Along Airport Road, Beside Centenary City, Abuja',
                'property_type' => 'Terrace',
                'description' => "Africa's First Polo & Golf Resort. Premium 150SQM Estate Land allocated for a contemporary 3 Bedroom Terrace Duplex. Title: FCDA Approved. World-Class Amenities: Mountain Resort, Golf Course, Cable Car, Polo Ground, Value Proposition, and Fitness Center.",
                'price' => 12000000.00,
                'available_units' => 20,
                'images' => ['properties/hutu_terrace_150sqm.jpg'],
            ],
            [
                'name' => 'Hutu Prestige - 4 Bedroom Terrace Duplex (250 SQM)',
                'estate_name' => 'Hutu Prestige Polo Lake Resort',
                'location' => 'Along Airport Road, Beside Centenary City, Abuja',
                'property_type' => 'Terrace',
                'description' => "Africa's First Polo & Golf Resort. Exclusive 250SQM Estate Land allocated for an ultra-luxury 4 Bedroom Terrace Duplex. Title: FCDA Approved. Features: Mountain Resort, Golf Course, Cable Car, Polo Ground, Value Proposition, and Fitness Center.",
                'price' => 20000000.00,
                'available_units' => 15,
                'images' => ['properties/hutu_terrace_250sqm.jpg'],
            ],
            [
                'name' => 'Hutu Prestige - 5 Bedroom Fully Detached Duplex (400 SQM)',
                'estate_name' => 'Hutu Prestige Polo Lake Resort',
                'location' => 'Along Airport Road, Beside Centenary City, Abuja',
                'property_type' => 'Duplex',
                'description' => "Africa's First Polo & Golf Resort. Signature 400SQM Estate Land allocated for a palatial 5 Bedroom Fully Detached Duplex. Title: FCDA Approved. Features: Mountain Resort, Championship Golf Course, Scenic Cable Car, Polo Ground, Value Proposition, and State-of-the-Art Fitness Center.",
                'price' => 32000000.00,
                'available_units' => 10,
                'images' => ['properties/hutu_detached_400sqm.jpg'],
            ],
        ];

        foreach ($properties as $data) {
            $property = Property::updateOrCreate(
                ['name' => $data['name']],
                $data
            );

            // Generate units for each property
            for ($i = 1; $i <= 5; $i++) {
                \App\Models\PropertyUnit::updateOrCreate(
                    [
                        'property_id' => $property->id,
                        'unit_number' => 'Plot ' . str_pad($i, 2, '0', STR_PAD_LEFT) . ' - Block ' . chr(65 + ($property->id % 5))
                    ],
                    [
                        'unit_type' => $property->property_type,
                        'size_sqm' => str_contains($property->name, '150') ? 150.00 : (str_contains($property->name, '250') ? 250.00 : 400.00),
                        'price' => $property->price,
                        'status' => 'available',
                        'description' => $property->description,
                        'features' => ['Mountain Resort', 'Golf Course', 'Cable Car', 'Polo Ground', 'FCDA Approved', 'Fitness Center']
                    ]
                );
            }
        }
    }
}
