<?php

namespace Database\Factories;

use App\Models\Lead;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeadFactory extends Factory
{
    protected $model = Lead::class;

    public function definition(): array
    {
        $names = [
            'Chinedu Okafor', 'Funke Adebayo', 'Aisha Yusuf', 'Olumide Johnson', 
            'Emeka Obi', 'Yetunde Balogun', 'Tunde Williams', 'Ngozi Ezekwesili', 
            'Abubakar Bello', 'Chioma Nwachukwu', 'Fatima Musa', 'Segun Olatunji'
        ];

        $locations = ['Lekki', 'Ikeja', 'Ikoyi', 'Wuse', 'Gbagada', 'Surulere'];
        $budgets = ['₦10M - ₦30M', '₦30M - ₦60M', '₦60M - ₦100M', '₦100M+'];
        $sources = ['Website', 'Referral', 'Social Media', 'WhatsApp', 'Cold Call', 'Billboard'];
        $statuses = ['New', 'Contacted', 'Follow Up', 'Inspection Scheduled', 'Negotiation', 'Payment Processing', 'Closed Won', 'Closed Lost'];

        $name = fake()->randomElement($names);
        $phone = '+234' . fake()->randomElement(['803', '805', '812', '703', '902']) . fake()->numerify('#######');

        return [
            'full_name' => $name,
            'phone_number' => $phone,
            'whatsapp_number' => fake()->boolean(80) ? $phone : null,
            'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
            'budget_range' => fake()->randomElement($budgets),
            'property_interest_id' => null, // Override in seeder
            'preferred_location' => fake()->randomElement($locations),
            'lead_source' => fake()->randomElement($sources),
            'assigned_to' => null, // Override in seeder
            'status' => fake()->randomElement($statuses),
            'notes' => fake()->sentence(10),
        ];
    }
}
