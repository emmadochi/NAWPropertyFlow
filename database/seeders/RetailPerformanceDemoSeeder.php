<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Property;
use App\Models\Lead;
use App\Models\Sale;
use App\Models\PaymentPlan;
use App\Models\PaymentMilestone;
use App\Models\Inspection;
use App\Models\FollowUp;
use App\Models\LeadActivity;
use App\Models\SalesWeeklyFieldLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class RetailPerformanceDemoSeeder extends Seeder
{
    /**
     * Run the database seeds with realistic retail sales team performance data.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $year = $now->year;
        $month = $now->month;
        $currentWeek = min(5, (int) ceil($now->day / 7));

        // 1. Ensure realistic properties exist
        $properties = [
            [
                'name' => 'The Oasis Terraces, Maitama II',
                'estate_name' => 'The Oasis Terraces',
                'location' => 'Maitama II, Abuja',
                'description' => '4-Bedroom Luxury Smart Terraces with BQ',
                'price' => 120000000,
                'status' => 'Available',
            ],
            [
                'name' => 'Buckcrest Horizon Heights, Guzape',
                'estate_name' => 'Horizon Heights',
                'location' => 'Guzape Hilltop, Abuja',
                'description' => 'Exclusive 3-Bedroom Contemporary Apartments with Panoramic City View',
                'price' => 75000000,
                'status' => 'Available',
            ],
            [
                'name' => 'Royal Palm Estate, Jabi Lake',
                'estate_name' => 'Royal Palm Estate',
                'location' => 'Jabi Lakefront, Abuja',
                'description' => 'Waterfront 5-Bedroom Detached Duplexes',
                'price' => 180000000,
                'status' => 'Available',
            ],
            [
                'name' => 'Grand View Boulevard, Katampe Extension',
                'estate_name' => 'Grand View Boulevard',
                'location' => 'Katampe Extension, Abuja',
                'description' => 'Serviced Residential Plots and Semi-Detached Villas',
                'price' => 45000000,
                'status' => 'Available',
            ],
        ];

        $propertyModels = [];
        foreach ($properties as $pData) {
            $propertyModels[] = Property::firstOrCreate(
                ['name' => $pData['name']],
                [
                    'estate_name' => $pData['estate_name'],
                    'location' => $pData['location'],
                    'description' => $pData['description'],
                    'price' => $pData['price'],
                    'status' => $pData['status'],
                    'bedrooms' => 4,
                    'bathrooms' => 4,
                ]
            );
        }

        // 2. Create 5 realistic Retail Sales Consultants
        $consultantsData = [
            [
                'name' => 'Joy Adeyemi',
                'email' => 'joy.adeyemi@propertyflow.com',
                'phone_number' => '+2348039124455',
                'role' => 'sales_executive',
                'job_title' => 'Senior Retail Sales Consultant',
                'department' => 'Marketing & Sales',
                'status' => 'active',
                'locations' => 'CAC Head Office, Banex Plaza, Ministry of Finance',
                'office_visits' => 6,
                'deal_value' => 45000000,
                'deal_client' => 'Chief Emeka Okafor',
                'deal_phone' => '+2348023349911',
                'deal_prop_idx' => 1,
                'topup_val' => 12500000,
                'topup_client' => 'Dr. Fatima Aliyu',
                'topup_phone' => '+2348187762233',
                'expected_count' => 3,
                'expected_notes' => 'Alhaji Mustapha committed ₦5M deposit Friday; Banex trader requesting 12mo plan',
                'inspections_count' => 4,
                'calls_count' => 38,
                'wa_count' => 45,
                'sms_count' => 12,
                'leads_count' => 18,
                'observations' => 'High demand for 3-bedroom terraces from Banex business owners; requested flexible 12-month installment schedule.',
                'manager_notes' => 'Commended for hitting target. Issue official payment plan breakdown to Banex prospects by Monday.'
            ],
            [
                'name' => 'Ibrahim Musa',
                'email' => 'ibrahim.musa@propertyflow.com',
                'phone_number' => '+2348058821144',
                'role' => 'sales_executive',
                'job_title' => 'Corporate Investment Consultant',
                'department' => 'Marketing & Sales',
                'status' => 'active',
                'locations' => 'NNPC Towers, Federal Secretariat, Central Bank Quarters',
                'office_visits' => 4,
                'deal_value' => 30000000,
                'deal_client' => 'Engr. Babatunde Adeleke',
                'deal_phone' => '+2348035512299',
                'deal_prop_idx' => 0,
                'topup_val' => 8000000,
                'topup_client' => 'Barr. Sandra Briggs',
                'topup_phone' => '+2348094451122',
                'expected_count' => 2,
                'expected_notes' => 'NNPC cooperative board reviewing 3-unit corporate allocation package',
                'inspections_count' => 3,
                'calls_count' => 29,
                'wa_count' => 36,
                'sms_count' => 8,
                'leads_count' => 14,
                'observations' => 'Corporate pitch with NNPC staff cooperative yielded 3 prospective group buyers. Pitch follow-up scheduled.',
                'manager_notes' => 'Provide discounted 5% off group booking rate approved by Management.'
            ],
            [
                'name' => 'Chidinma Okonkwo',
                'email' => 'chidinma.o@propertyflow.com',
                'phone_number' => '+2348126639900',
                'role' => 'sales_executive',
                'job_title' => 'Retail Acquisition Specialist',
                'department' => 'Marketing & Sales',
                'status' => 'active',
                'locations' => 'Wuse Market, Silverbird Galleria, Transcorp Hilton Area',
                'office_visits' => 5,
                'deal_value' => 15000000,
                'deal_client' => 'Hajia Zainab Doma',
                'deal_phone' => '+2348149987722',
                'deal_prop_idx' => 3,
                'topup_val' => 5000000,
                'topup_client' => 'Mr. Victor Osas',
                'topup_phone' => '+2348071123344',
                'expected_count' => 2,
                'expected_notes' => 'Awaiting diaspora wire transfer of ₦10M from UK client (Dr. Nwachukwu)',
                'inspections_count' => 2,
                'calls_count' => 24,
                'wa_count' => 31,
                'sms_count' => 10,
                'leads_count' => 12,
                'observations' => 'Diaspora buyers actively requesting virtual 3D tour video and certified true copy of title deeds.',
                'manager_notes' => 'Legal vault deeds sent to client solicitors.'
            ],
            [
                'name' => 'Farouk Bello',
                'email' => 'farouk.bello@propertyflow.com',
                'phone_number' => '+2348061122883',
                'role' => 'sales_agent',
                'job_title' => 'Field Canvassing Agent',
                'department' => 'Marketing & Sales',
                'status' => 'active',
                'locations' => 'Garki 2 Shopping Mall, Area 11 Ministries, Emab Plaza',
                'office_visits' => 2,
                'deal_value' => 0,
                'deal_client' => null,
                'deal_phone' => null,
                'deal_prop_idx' => 2,
                'topup_val' => 3500000,
                'topup_client' => 'Pastor Emmanuel Dare',
                'topup_phone' => '+2348028811776',
                'expected_count' => 4,
                'expected_notes' => 'Emab Plaza electronics distributor committed to ₦10M initial deposit',
                'inspections_count' => 3,
                'calls_count' => 34,
                'wa_count' => 28,
                'sms_count' => 6,
                'leads_count' => 16,
                'observations' => 'Conducted 3 site tours over the weekend. 2 prospects currently in contract review stage.',
                'manager_notes' => 'Focus on closing Emab distributor before end of month.'
            ],
            [
                'name' => 'Ngozi Eze',
                'email' => 'ngozi.eze@propertyflow.com',
                'phone_number' => '+2348135544778',
                'role' => 'sales_executive',
                'job_title' => 'Client Relationship Consultant',
                'department' => 'Marketing & Sales',
                'status' => 'active',
                'locations' => 'Abuja Enterprise Agency, Apo Resettlement, Legislative Quarters',
                'office_visits' => 3,
                'deal_value' => 22000000,
                'deal_client' => 'Hon. Kingsley Anosike',
                'deal_phone' => '+2348037748833',
                'deal_prop_idx' => 1,
                'topup_val' => 0,
                'topup_client' => null,
                'topup_phone' => null,
                'expected_count' => 1,
                'expected_notes' => '₦7.5M 2nd milestone installment confirmed for next week',
                'inspections_count' => 2,
                'calls_count' => 21,
                'wa_count' => 25,
                'sms_count' => 5,
                'leads_count' => 10,
                'observations' => 'Legislative Quarters walk-in prospects expressed strong satisfaction with structural finishing and estate security.',
                'manager_notes' => 'Ensure allocation documentation is prepared promptly for Hon. Kingsley.'
            ],
        ];

        foreach ($consultantsData as $c) {
            $user = User::firstOrCreate(
                ['email' => $c['email']],
                [
                    'name' => $c['name'],
                    'password' => Hash::make('password123'),
                    'role' => $c['role'],
                    'job_title' => $c['job_title'],
                    'department' => $c['department'],
                    'phone_number' => $c['phone_number'],
                    'status' => 'active',
                ]
            );

            // Update user title/status if already existed
            $user->update([
                'role' => $c['role'],
                'job_title' => $c['job_title'],
                'status' => 'active',
            ]);

            $prop = $propertyModels[$c['deal_prop_idx']] ?? $propertyModels[0];

            // 3. Create Leads for this consultant
            $leadNames = [
                'Alhaji Garba Shehu', 'Mrs. Folake Davies', 'Chief Mike Igwe', 'Dr. Amina Yusuf',
                'Engr. Kenneth Cole', 'Mrs. Blessing Umeh', 'Alhaji Sani Dangote', 'Barr. Austin Peters',
                'Mrs. Chioma Adele', 'Mr. Segun Oladipo', 'Dr. Charles Obi', 'Hajia Aisha Balarabe'
            ];

            $createdLeads = [];
            for ($lIdx = 0; $lIdx < min($c['leads_count'], count($leadNames)); $lIdx++) {
                $lName = $leadNames[$lIdx];
                $createdLeads[] = Lead::firstOrCreate(
                    [
                        'full_name' => "{$lName} ({$user->name})",
                        'assigned_to' => $user->id,
                    ],
                    [
                        'phone_number' => '+23480' . rand(10000000, 99999999),
                        'email' => strtolower(str_replace(' ', '.', $lName)) . rand(10, 99) . '@gmail.com',
                        'lead_source' => ($lIdx % 2 === 0) ? 'Outreach Canvassing' : 'Referral',
                        'outreach_location' => explode(',', $c['locations'])[0] ?? 'Banex Plaza',
                        'status' => ($lIdx === 0) ? 'Closed Won' : (($lIdx < 3) ? 'Qualified' : 'Contacted'),
                        'lead_temperature' => ($lIdx < 2) ? 'hot' : 'warm',
                        'budget_range' => '₦50M - ₦100M',
                        'created_at' => $now->copy()->subDays(rand(1, 10)),
                        'updated_at' => $now->copy()->subHours(rand(1, 24)),
                    ]
                );
            }

            $primaryLead = !empty($createdLeads) ? $createdLeads[0] : null;

            // 4. Closed Sale
            if ($c['deal_value'] > 0 && $primaryLead) {
                $sale = Sale::firstOrCreate(
                    [
                        'sales_officer_id' => $user->id,
                        'lead_id' => $primaryLead->id,
                    ],
                    [
                        'property_id' => $prop->id,
                        'deal_value' => $c['deal_value'],
                        'base_deal_value' => $c['deal_value'],
                        'units_purchased' => 1,
                        'status' => 'Closed Won',
                        'deal_closed_at' => $now->copy()->subDays(rand(1, 5)),
                        'created_at' => $now->copy()->subDays(rand(1, 5)),
                    ]
                );

                // Create Payment Plan and Paid Milestone
                $plan = PaymentPlan::firstOrCreate(
                    ['sale_id' => $sale->id],
                    [
                        'duration_months' => 6,
                        'plan_type' => 'Milestone Installments',
                        'base_deal_value' => $c['deal_value'],
                        'total_amount' => $c['deal_value'],
                        'amount_paid' => $c['deal_value'],
                        'balance' => 0,
                        'number_of_installments' => 3,
                        'status' => 'Active',
                    ]
                );

                PaymentMilestone::firstOrCreate(
                    [
                        'payment_plan_id' => $plan->id,
                        'label' => 'Initial Deposit / Outright Receipt',
                    ],
                    [
                        'amount_due' => $c['deal_value'],
                        'amount_paid' => $c['deal_value'],
                        'paid_at' => $now->copy()->subDays(rand(1, 4)),
                        'status' => 'Paid',
                        'bank_reference' => 'BHL/TX/' . rand(100000, 999999),
                    ]
                );
            }

            // 5. Milestone Top-up (Part Payment)
            if ($c['topup_val'] > 0) {
                $topupLead = Lead::firstOrCreate(
                    [
                        'full_name' => $c['topup_client'] ?? 'Topup Client',
                        'assigned_to' => $user->id,
                    ],
                    [
                        'phone_number' => $c['topup_phone'] ?? '+2348011223344',
                        'email' => 'client.' . rand(100, 999) . '@propertyflow.com',
                        'lead_source' => 'Direct Client',
                        'status' => 'Closed Won',
                        'created_at' => $now->copy()->subMonths(1),
                    ]
                );

                $topupSale = Sale::firstOrCreate(
                    [
                        'sales_officer_id' => $user->id,
                        'lead_id' => $topupLead->id,
                    ],
                    [
                        'property_id' => $propertyModels[1]->id ?? $prop->id,
                        'deal_value' => $c['topup_val'] * 3,
                        'base_deal_value' => $c['topup_val'] * 3,
                        'status' => 'Closed Won',
                        'deal_closed_at' => $now->copy()->subMonths(1),
                    ]
                );

                $topupPlan = PaymentPlan::firstOrCreate(
                    ['sale_id' => $topupSale->id],
                    [
                        'duration_months' => 12,
                        'total_amount' => $c['topup_val'] * 3,
                        'amount_paid' => $c['topup_val'],
                        'balance' => $c['topup_val'] * 2,
                        'status' => 'Active',
                    ]
                );

                PaymentMilestone::firstOrCreate(
                    [
                        'payment_plan_id' => $topupPlan->id,
                        'label' => 'Stage 2 Milestone Payment',
                    ],
                    [
                        'amount_due' => $c['topup_val'],
                        'amount_paid' => $c['topup_val'],
                        'paid_at' => $now->copy()->subDays(rand(1, 6)),
                        'status' => 'Paid',
                        'bank_reference' => 'BHL/TOPUP/' . rand(100000, 999999),
                    ]
                );
            }

            // 6. Site Inspections
            for ($ins = 0; $ins < $c['inspections_count']; $ins++) {
                $inspLead = $createdLeads[$ins % count($createdLeads)] ?? $primaryLead;
                Inspection::firstOrCreate(
                    [
                        'assigned_to' => $user->id,
                        'lead_id' => $inspLead?->id,
                        'inspection_date' => $now->copy()->subDays(rand(1, 5))->setTime(10 + $ins, 0),
                    ],
                    [
                        'property_id' => $prop->id,
                        'status' => 'Completed',
                        'notes' => 'Client thoroughly inspected floor plan, foundation, and boundary beacons.',
                    ]
                );
            }

            // 7. Verified Calls & WhatsApp Engagements
            for ($cl = 0; $cl < min(6, count($createdLeads)); $cl++) {
                $touchLead = $createdLeads[$cl];
                
                // Call Activity
                LeadActivity::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'lead_id' => $touchLead->id,
                        'activity_type' => 'Phone Call',
                    ],
                    [
                        'description' => 'Detailed conversation reviewing pricing schedule and property inspection date.',
                        'created_at' => $now->copy()->subDays(rand(1, 6)),
                    ]
                );

                // WhatsApp Activity
                LeadActivity::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'lead_id' => $touchLead->id,
                        'activity_type' => 'WhatsApp Discussion',
                    ],
                    [
                        'description' => 'Shared estate brochure, floor plan PDF, and milestone payment schedule via WhatsApp.',
                        'created_at' => $now->copy()->subDays(rand(1, 6)),
                    ]
                );
            }

            // 8. Weekly Field Log (Offline Canvassing & Notes)
            if (Schema::hasTable('sales_weekly_field_logs')) {
                SalesWeeklyFieldLog::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'year' => $year,
                        'month' => $month,
                        'week_number' => $currentWeek,
                    ],
                    [
                        'canvassing_locations' => $c['locations'],
                        'office_visits_count' => $c['office_visits'],
                        'expected_payments_count' => $c['expected_count'],
                        'expected_payments_notes' => $c['expected_notes'],
                        'observations_recommendations' => $c['observations'],
                        'manager_feedback' => $c['manager_notes'],
                    ]
                );
            }
        }
    }
}
