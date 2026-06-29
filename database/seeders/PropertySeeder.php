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
                'name' => 'The Orange Valley Heights',
                'estate_name' => 'Orange Valley Estate',
                'location' => 'Lekki Phase 1, Lagos',
                'property_type' => 'Duplex',
                'description' => 'A luxury 5-bedroom fully detached duplex with a pent house, swimming pool, and boys quarters.',
                'price' => 180000000.00,
                'available_units' => 5,
                'images' => ['properties/penthouse.jpg'],
            ],
            [
                'name' => 'Victoria Garden Court',
                'estate_name' => 'Victoria Crest Estate',
                'location' => 'Ikeja GRA, Lagos',
                'property_type' => 'Terrace',
                'description' => 'A serene 4-bedroom terrace house with ample parking space and 24/7 power supply.',
                'price' => 95000000.00,
                'available_units' => 8,
                'images' => ['properties/terrace.jpg'],
            ],
            [
                'name' => 'Pinecrest Haven Lands',
                'estate_name' => 'Pinecrest Gardens',
                'location' => 'Epe, Lagos',
                'property_type' => 'Land',
                'description' => 'Premium dry plots of land measuring 600sqm, with certificate of occupancy (C of O) and excellent road accessibility.',
                'price' => 15000000.00,
                'available_units' => 30,
                'images' => ['properties/land.jpg'],
            ],
            [
                'name' => 'Maitama Court Apartments',
                'estate_name' => 'Maitama Heights',
                'location' => 'Maitama, Abuja',
                'property_type' => 'Flat',
                'description' => 'Sophisticated 3-bedroom serviced apartment with top-notch security system, gym, and proximity to central Abuja.',
                'price' => 140000000.00,
                'available_units' => 4,
                'images' => ['properties/flat.jpg'],
            ],
            [
                'name' => 'Banana Island Shoreline',
                'estate_name' => 'Banana Island View',
                'location' => 'Ikoyi, Lagos',
                'property_type' => 'Duplex',
                'description' => 'Exquisite waterfront 6-bedroom smart home duplex with world-class facilities and private cinema.',
                'price' => 450000000.00,
                'available_units' => 2,
                'images' => ['properties/banana_duplex.jpg'],
            ],
        ];

        foreach ($properties as $property) {
            Property::create($property);
        }
    }
}
