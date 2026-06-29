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

        // 1. Lead Chinedu Okafor - Closed Won Deal
        $lead1 = Lead::create([
            'full_name' => 'Chinedu Okafor',
            'phone_number' => '+2348031234567',
            'whatsapp_number' => '+2348031234567',
            'email' => 'chinedu.okafor@example.com',
            'budget_range' => '₦100M+',
            'property_interest_id' => $properties->where('property_type', 'Duplex')->first()->id,
            'preferred_location' => 'Lekki',
            'lead_source' => 'Website',
            'assigned_to' => $executives->first()->id,
            'status' => 'Closed Won',
            'notes' => 'Looking for a smart home in Lekki. Prefers prompt communication.',
        ]);

        // Activities for Lead 1
        LeadActivity::create([
            'lead_id' => $lead1->id,
            'user_id' => $executives->first()->id,
            'activity_type' => 'Created',
            'description' => 'Lead imported from Website form.',
        ]);
        LeadActivity::create([
            'lead_id' => $lead1->id,
            'user_id' => $executives->first()->id,
            'activity_type' => 'Status Changed',
            'description' => 'Status updated from New to Contacted after introductory call.',
        ]);
        LeadActivity::create([
            'lead_id' => $lead1->id,
            'user_id' => $executives->first()->id,
            'activity_type' => 'Inspection Scheduled',
            'description' => 'Site inspection scheduled for Orange Valley Heights.',
        ]);
        LeadActivity::create([
            'lead_id' => $lead1->id,
            'user_id' => $executives->first()->id,
            'activity_type' => 'Sale Closed',
            'description' => 'Sale recorded successfully. Deal closed!',
        ]);

        // Inspection for Lead 1
        Inspection::create([
            'lead_id' => $lead1->id,
            'property_id' => $lead1->property_interest_id,
            'assigned_to' => $executives->first()->id,
            'inspection_date' => Carbon::now()->subDays(5),
            'status' => 'Completed',
            'notes' => 'Client fell in love with the layout and structure. Requested pricing details.',
        ]);

        // Sale for Lead 1
        Sale::create([
            'lead_id' => $lead1->id,
            'property_id' => $lead1->property_interest_id,
            'sales_officer_id' => $executives->first()->id,
            'deal_value' => 180000000.00,
            'units_purchased' => 1,
            'status' => 'Closed Won',
            'payment_receipt' => 'receipts/chinedu_payment_receipt.pdf',
            'deal_closed_at' => Carbon::now()->subDays(2),
        ]);

        // Document for Lead 1
        Document::create([
            'lead_id' => $lead1->id,
            'name' => 'Chinedu KYC Passport',
            'file_path' => 'documents/chinedu_passport.pdf',
            'category' => 'KYC',
            'uploaded_by' => $executives->first()->id,
        ]);


        // 2. Lead Funke Adebayo - Follow Up, Call Due Today
        $lead2 = Lead::create([
            'full_name' => 'Funke Adebayo',
            'phone_number' => '+2348057778888',
            'whatsapp_number' => '+2348057778888',
            'email' => 'funke.adebayo@example.com',
            'budget_range' => '₦60M - ₦100M',
            'property_interest_id' => $properties->where('property_type', 'Terrace')->first()->id,
            'preferred_location' => 'Ikeja',
            'lead_source' => 'Social Media',
            'assigned_to' => $executives->last()->id,
            'status' => 'Follow Up',
            'notes' => 'Prefers calls in the afternoon. Interested in payment plans.',
        ]);

        LeadActivity::create([
            'lead_id' => $lead2->id,
            'user_id' => $executives->last()->id,
            'activity_type' => 'Created',
            'description' => 'Lead created manually by sales officer.',
        ]);

        // Follow up due today
        FollowUp::create([
            'lead_id' => $lead2->id,
            'type' => 'Call',
            'due_date' => Carbon::now()->hour(14)->minute(0),
            'notes' => 'Call to discuss the 12-month payment installment options.',
            'status' => 'Pending',
        ]);


        // 3. Lead Aisha Yusuf - Inspection Scheduled Tomorrow
        $lead3 = Lead::create([
            'full_name' => 'Aisha Yusuf',
            'phone_number' => '+2348123334444',
            'whatsapp_number' => null,
            'email' => 'aisha.yusuf@example.com',
            'budget_range' => '₦10M - ₦30M',
            'property_interest_id' => $properties->where('property_type', 'Land')->first()->id,
            'preferred_location' => 'Epe',
            'lead_source' => 'Referral',
            'assigned_to' => $executives->first()->id,
            'status' => 'Inspection Scheduled',
            'notes' => 'Purchasing land for long term investment.',
        ]);

        LeadActivity::create([
            'lead_id' => $lead3->id,
            'user_id' => $executives->first()->id,
            'activity_type' => 'Created',
            'description' => 'Lead referred by client Mr. Johnson.',
        ]);

        // Inspection tomorrow
        Inspection::create([
            'lead_id' => $lead3->id,
            'property_id' => $lead3->property_interest_id,
            'assigned_to' => $executives->first()->id,
            'inspection_date' => Carbon::tomorrow()->hour(10)->minute(0),
            'status' => 'Scheduled',
            'notes' => 'Meeting at the Epe toll gate and driving to Pinecrest Haven Lands together.',
        ]);


        // 4. Lead Abubakar Bello - Overdue Follow Up
        $lead4 = Lead::create([
            'full_name' => 'Abubakar Bello',
            'phone_number' => '+2347039990000',
            'whatsapp_number' => '+2347039990000',
            'email' => 'abubakar.bello@example.com',
            'budget_range' => '₦60M - ₦100M',
            'property_interest_id' => $properties->where('property_type', 'Flat')->first()->id,
            'preferred_location' => 'Maitama',
            'lead_source' => 'Cold Call',
            'assigned_to' => $executives->last()->id,
            'status' => 'New',
            'notes' => 'Interested in Maitama serviced apartments. Needs prompt follow up.',
        ]);

        LeadActivity::create([
            'lead_id' => $lead4->id,
            'user_id' => $executives->last()->id,
            'activity_type' => 'Created',
            'description' => 'Lead created after cold calling.',
        ]);

        // Overdue follow up
        FollowUp::create([
            'lead_id' => $lead4->id,
            'type' => 'Meeting',
            'due_date' => Carbon::now()->subDays(3)->hour(11)->minute(0),
            'notes' => 'In-person meeting at their office to share brochure.',
            'status' => 'Pending',
        ]);


        // 5. Lead Segun Olatunji - Negotiation
        $lead5 = Lead::create([
            'full_name' => 'Segun Olatunji',
            'phone_number' => '+2348039991111',
            'whatsapp_number' => '+2348039991111',
            'email' => 'segun.olatunji@example.com',
            'budget_range' => '₦100M+',
            'property_interest_id' => $properties->where('property_type', 'Duplex')->last()->id, // Banana Island Duplex
            'preferred_location' => 'Ikoyi',
            'lead_source' => 'Website',
            'assigned_to' => $executives->first()->id,
            'status' => 'Negotiation',
            'notes' => 'Negotiating the final selling price. Looking for a 10% discount.',
        ]);

        LeadActivity::create([
            'lead_id' => $lead5->id,
            'user_id' => $executives->first()->id,
            'activity_type' => 'Created',
            'description' => 'Lead captured from Website landing page.',
        ]);

        // Completed Inspection
        Inspection::create([
            'lead_id' => $lead5->id,
            'property_id' => $lead5->property_interest_id,
            'assigned_to' => $executives->first()->id,
            'inspection_date' => Carbon::now()->subDays(7),
            'status' => 'Completed',
            'notes' => 'Site tour of Banana Island Duplex went well. Client started negotiation immediately.',
        ]);

        // Follow up due tomorrow
        FollowUp::create([
            'lead_id' => $lead5->id,
            'type' => 'Call',
            'due_date' => Carbon::tomorrow()->hour(15)->minute(0),
            'notes' => 'Call client to present the final approved discount from management.',
            'status' => 'Pending',
        ]);
    }
}
