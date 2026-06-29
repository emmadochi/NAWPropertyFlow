<?php

namespace Database\Factories;

use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyFactory extends Factory
{
    protected $model = Property::class;

    public function definition(): array
    {
        $locations = ['Lekki Phase 1, Lagos', 'Ikeja GRA, Lagos', 'Ikoyi, Lagos', 'Wuse 2, Abuja', 'Maitama, Abuja', 'Gbagada, Lagos'];
        $estates = ['Orange Valley Estate', 'Pinecrest Gardens', 'Blueberry Hill', 'Victoria Crest', 'Palm Heights'];
        $types = ['Duplex', 'Terrace', 'Flat', 'Land'];

        $type = fake()->randomElement($types);
        $name = fake()->colorName() . ' ' . $type;

        return [
            'name' => $name,
            'estate_name' => fake()->randomElement($estates),
            'location' => fake()->randomElement($locations),
            'property_type' => $type,
            'description' => fake()->paragraph(3),
            'price' => fake()->randomElement([15000000.00, 25000000.00, 45000000.00, 75000000.00, 120000000.00, 180000000.00]),
            'available_units' => fake()->numberBetween(1, 15),
            'images' => [
                'properties/sample1.jpg',
                'properties/sample2.jpg',
                'properties/sample3.jpg'
            ],
        ];
    }
}
