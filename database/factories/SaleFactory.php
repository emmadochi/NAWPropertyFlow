<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        return [
            'lead_id'          => Lead::factory(),
            'property_id'      => Property::factory(),
            'sales_officer_id' => User::factory(),
            'deal_value'       => $this->faker->numberBetween(500000, 10000000),
            'units_purchased'  => 1,
            'status'           => 'Closed Won',
        ];
    }
}
