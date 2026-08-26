<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lead;
use App\Models\Property;
use App\Models\Branch;
use App\Models\User;

class EmmanuelLeadSeeder extends Seeder
{
    public function run(): void
    {
        $property = Property::where('name', 'like', '%Orange Valley%')->first() ?? Property::first();
        $branch = Branch::first();
        $agent = User::where('email', 'like', '%admin%')->first() ?? User::first();

        $lead = Lead::updateOrCreate(
            ['email' => 'emmadochi@gmail.com'],
            [
                'full_name'            => 'Emmanuel Obinezu',
                'phone_number'         => '+234 803 555 0192',
                'whatsapp_number'      => '+234 803 555 0192',
                'preferred_location'   => 'Plot 402, Diplomatic Zone, Maitama, Abuja',
                'budget_range'         => '₦85,000,000 - ₦180,000,000',
                'lead_source'          => 'VIP Executive Referral',
                'status'               => 'Hot',
                'property_interest_id' => $property ? $property->id : null,
                'branch_id'            => $branch ? $branch->id : null,
                'assigned_to'          => $agent ? $agent->id : null,
                'notes'                => 'High-Net-Worth VIP Client interested in luxury terrace duplex / prime serviced land in Abuja with flexible installment schedule.',
            ]
        );

        $lead->getOrCreatePortalToken();
    }
}
