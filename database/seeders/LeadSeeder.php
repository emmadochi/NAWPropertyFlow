<?php

namespace Database\Seeders;

use App\Models\Lead;
use App\Models\Property;
use App\Models\User;
use App\Models\Inspection;
use App\Models\FollowUp;
use App\Models\Sale;
use App\Models\Document;
use App\Models\LeadActivity;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LeadSeeder extends Seeder
{
    public function run(): void
    {
        $properties = Property::all();
        $executives = User::where('role', 'sales_executive')->get();
        $manager = User::where('role', 'sales_manager')->first();

        if ($properties->isEmpty() || $executives->isEmpty()) {
            return;
        }

        $exec1 = $executives->first();
        $exec2 = $executives->last();

        $leadsData = [
            // 1. Closed Won
            [
                'full_name' => 'Chinedu Okafor',
                'phone_number' => '+2348031234567',
                'whatsapp_number' => '+2348031234567',
                'email' => 'chinedu.okafor@example.com',
                'budget_range' => '₦100M+',
                'type' => 'Duplex',
                'location' => 'Guzape, Abuja',
                'source' => 'Website',
                'assigned_to' => $exec1->id,
                'status' => 'Closed Won',
                'notes' => 'Executive buyer from NNPC. Purchased 5-Bedroom Detached Villa.',
                'portal_token' => Str::random(32),
            ],
            // 2. Follow Up (Call Due Today)
            [
                'full_name' => 'Funke Adebayo',
                'phone_number' => '+2348057778888',
                'whatsapp_number' => '+2348057778888',
                'email' => 'funke.adebayo@example.com',
                'budget_range' => '₦60M - ₦100M',
                'type' => 'Terrace',
                'location' => 'Maitama, Abuja',
                'source' => 'Social Media',
                'assigned_to' => $exec2->id,
                'status' => 'Follow Up',
                'notes' => 'Requested 12-month payment installment schedule for 4-Bedroom Terrace.',
                'portal_token' => Str::random(32),
            ],
            // 3. Inspection Scheduled
            [
                'full_name' => 'Dr. Aisha Yusuf',
                'phone_number' => '+2348123334444',
                'whatsapp_number' => '+2348123334444',
                'email' => 'aisha.yusuf@example.com',
                'budget_range' => '₦20M - ₦40M',
                'type' => 'Terrace',
                'location' => 'Airport Road, Abuja',
                'source' => 'Referral',
                'assigned_to' => $exec1->id,
                'status' => 'Inspection Scheduled',
                'notes' => 'Medical doctor at National Hospital. Site visit booked for tomorrow.',
                'portal_token' => Str::random(32),
            ],
            // 4. New Lead (Cold Outreach)
            [
                'full_name' => 'Alhaji Abubakar Bello',
                'phone_number' => '+2347039990000',
                'whatsapp_number' => '+2347039990000',
                'email' => 'abubakar.bello@example.com',
                'budget_range' => '₦100M+',
                'type' => 'Duplex',
                'location' => 'Katampe Extension, Abuja',
                'source' => 'Cold Call',
                'assigned_to' => $exec2->id,
                'status' => 'New',
                'notes' => 'Looking for corner piece luxury plot with mountain views.',
                'portal_token' => Str::random(32),
            ],
            // 5. Negotiation
            [
                'full_name' => 'Engr. Segun Olatunji',
                'phone_number' => '+2348039991111',
                'whatsapp_number' => '+2348039991111',
                'email' => 'segun.olatunji@example.com',
                'budget_range' => '₦80M - ₦120M',
                'type' => 'Duplex',
                'location' => 'Jabi, Abuja',
                'source' => 'Billboard Campaign',
                'assigned_to' => $exec1->id,
                'status' => 'Negotiation',
                'notes' => 'Negotiating 5% cash discount for upfront payment on 2 units.',
                'portal_token' => Str::random(32),
            ],
            // 6. Diaspora Lead (UK)
            [
                'full_name' => 'Mrs. Ngozi Eze (London, UK)',
                'phone_number' => '+447911123456',
                'whatsapp_number' => '+447911123456',
                'email' => 'ngozi.eze.uk@example.com',
                'budget_range' => '₦150M+',
                'type' => 'Duplex',
                'location' => 'Centenary City, Abuja',
                'source' => 'Diaspora Campaign',
                'assigned_to' => $exec1->id,
                'status' => 'Inspection Scheduled',
                'notes' => 'Diaspora investor. Brother will represent her for video site tour.',
                'portal_token' => Str::random(32),
            ],
            // 7. Commercial Investor
            [
                'full_name' => 'Barrister Fola Williams',
                'phone_number' => '+2348028884444',
                'whatsapp_number' => '+2348028884444',
                'email' => 'fola.williams@lawfirm.ng',
                'budget_range' => '₦200M+',
                'type' => 'Terrace',
                'location' => 'Wuse II, Abuja',
                'source' => 'Realtor Network',
                'assigned_to' => $exec2->id,
                'status' => 'Follow Up',
                'notes' => 'Purchasing 3 terrace units for short-let Airbnb operations.',
                'portal_token' => Str::random(32),
            ],
            // 8. Contract Sent
            [
                'full_name' => 'Captain Ibrahim Danjuma',
                'phone_number' => '+2348145556677',
                'whatsapp_number' => '+2348145556677',
                'email' => 'ibrahim.danjuma@aviation.ng',
                'budget_range' => '₦70M - ₦100M',
                'type' => 'Duplex',
                'location' => 'Airport Road, Abuja',
                'source' => 'Exhibition / Expo',
                'assigned_to' => $exec1->id,
                'status' => 'Contract Sent',
                'notes' => 'Draft Deed of Assignment sent for legal review.',
                'portal_token' => Str::random(32),
            ],
            // 9. Hot Prospect
            [
                'full_name' => 'Chioma Nwachukwu',
                'phone_number' => '+2348091122334',
                'whatsapp_number' => '+2348091122334',
                'email' => 'chioma.n@techcorp.ng',
                'budget_range' => '₦30M - ₦60M',
                'type' => 'Terrace',
                'location' => 'Lugbe Scheme, Abuja',
                'source' => 'Instagram Ads',
                'assigned_to' => $exec2->id,
                'status' => 'Contacted',
                'notes' => 'Fintech executive. Ready to pay 30% initial deposit.',
                'portal_token' => Str::random(32),
            ],
            // 10. Closed Won Diaspora Deal (Banana Island Villa)
            [
                'full_name' => 'Chief Obinna Okonkwo (Lagos / London)',
                'phone_number' => '+447812999888',
                'whatsapp_number' => '+447812999888',
                'email' => 'obinna.okonkwo@diasporacapital.co.uk',
                'budget_range' => '₦500M+',
                'type' => 'Duplex',
                'location' => 'Banana Island, Lagos',
                'source' => 'Diaspora Campaign',
                'assigned_to' => $exec2->id,
                'status' => 'Closed Won',
                'notes' => 'Diaspora investor acquisition of Banana Island Waterfront Villa for ₦650M. Payment verified.',
                'portal_token' => Str::random(32),
            ],
        ];

        foreach ($leadsData as $data) {
            $prop = $properties->where('property_type', $data['type'])->first() ?? $properties->random();
            if (str_contains($data['full_name'], 'Obinna')) {
                $prop = $properties->where('name', 'Banana Island Marina Court - 5 Bedroom Waterfront Detached Villa')->first() ?? $prop;
            } elseif (str_contains($data['full_name'], 'Chinedu')) {
                $prop = $properties->where('name', 'Guzape Royal Crest - 5 Bedroom Smart Luxury Villa')->first() ?? $prop;
            }

            $lead = Lead::updateOrCreate(
                ['email' => $data['email']],
                [
                    'full_name' => $data['full_name'],
                    'phone_number' => $data['phone_number'],
                    'whatsapp_number' => $data['whatsapp_number'],
                    'email' => $data['email'],
                    'budget_range' => $data['budget_range'],
                    'property_interest_id' => $prop->id,
                    'preferred_location' => $data['location'],
                    'lead_source' => $data['source'],
                    'assigned_to' => $data['assigned_to'],
                    'status' => $data['status'],
                    'notes' => $data['notes'],
                    'portal_token' => $data['portal_token'],
                ]
            );

            // Create initial activity
            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => $lead->assigned_to,
                'activity_type' => 'Created',
                'description' => 'Lead logged from ' . $lead->lead_source . '.',
            ]);

            // Add inspection or follow up based on status
            if ($lead->status === 'Inspection Scheduled') {
                Inspection::create([
                    'lead_id' => $lead->id,
                    'property_id' => $lead->property_interest_id,
                    'assigned_to' => $lead->assigned_to,
                    'inspection_date' => Carbon::tomorrow()->hour(11)->minute(0),
                    'status' => 'Scheduled',
                    'notes' => 'Client visitation scheduled. Logistics vehicle reserved.',
                ]);
            } elseif ($lead->status === 'Follow Up') {
                FollowUp::create([
                    'lead_id' => $lead->id,
                    'type' => 'Call',
                    'due_date' => Carbon::now()->addHours(2),
                    'notes' => 'Follow up on payment milestone terms.',
                    'status' => 'Pending',
                ]);
            } elseif ($lead->status === 'Closed Won') {
                $dealValue = str_contains($lead->full_name, 'Obinna') ? 650000000.00 : 180000000.00;
                $closedDate = str_contains($lead->full_name, 'Obinna') ? Carbon::now()->subMonths(1) : Carbon::now()->subDays(3);

                Sale::updateOrCreate(
                    ['lead_id' => $lead->id],
                    [
                        'property_id' => $lead->property_interest_id,
                        'sales_officer_id' => $lead->assigned_to,
                        'deal_value' => $dealValue,
                        'units_purchased' => 1,
                        'status' => 'Closed Won',
                        'payment_receipt' => 'receipts/verified_payment_receipt.pdf',
                        'deal_closed_at' => $closedDate,
                    ]
                );
            }
        }
    }
}
