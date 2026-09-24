<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SeedAuditDemoCommand extends Command
{
    protected $signature = 'crm:seed-audit-demo {--tenant=} {--clear : Clear demo test data instead of seeding}';
    protected $description = 'Seed or clear comprehensive test data for the Lead Quality & Executive Audit Board';

    public function handle(): int
    {
        try {
            $tenantQuery = \App\Models\Tenant::query();
            if ($tenantId = $this->option('tenant')) {
                $tenantQuery->where('id', $tenantId);
            }
            $tenants = $tenantQuery->get();
        } catch (\Throwable $e) {
            $tenants = collect();
        }

        if ($tenants->isNotEmpty()) {
            foreach ($tenants as $tenant) {
                $this->info("--> Initializing Tenancy for: [{$tenant->id}] (" . ($tenant->company_name ?? $tenant->id) . ")");
                $tenant->run(function () use ($tenant) {
                    $this->processAuditSeeding();
                });
                $this->info("✓ Successfully processed tenant: [{$tenant->id}]");
            }
            return Command::SUCCESS;
        }

        $this->processAuditSeeding();
        return Command::SUCCESS;
    }

    private function processAuditSeeding(): void
    {
        if ($this->option('clear')) {
            $testLeadIds = Lead::withoutGlobalScopes()
                ->where('lead_source', 'like', '[Demo/Test]%')
                ->pluck('id');

            LeadActivity::whereIn('lead_id', $testLeadIds)->delete();
            $deleted = Lead::withoutGlobalScopes()->whereIn('id', $testLeadIds)->delete();
            User::whereIn('email', ['chidinma.audit@bhl.com', 'tunde.audit@bhl.com', 'amina.audit@bhl.com'])->delete();

            $this->info("✅ Cleared {$deleted} test lead records and test audit accounts.");
            return;
        }

        // Self-healing: Ensure audit and engagement columns exist on leads table
        if (\Illuminate\Support\Facades\Schema::hasTable('leads')) {
            try {
                \Illuminate\Support\Facades\Schema::table('leads', function (\Illuminate\Database\Schema\Blueprint $table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('leads', 'outreach_location')) {
                        $table->string('outreach_location')->nullable()->after('lead_source');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('leads', 'last_contacted_at')) {
                        $table->dateTime('last_contacted_at')->nullable()->after('status');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('leads', 'last_contact_channel')) {
                        $table->string('last_contact_channel')->nullable()->after('last_contacted_at');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('leads', 'unreachable_count')) {
                        $table->integer('unreachable_count')->default(0)->after('last_contact_channel');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('leads', 'is_flagged_fake')) {
                        $table->boolean('is_flagged_fake')->default(false)->after('unreachable_count');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('leads', 'flagged_reason')) {
                        $table->string('flagged_reason')->nullable()->after('is_flagged_fake');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('leads', 'flagged_at')) {
                        $table->dateTime('flagged_at')->nullable()->after('flagged_reason');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('leads', 'lead_temperature')) {
                        $table->string('lead_temperature', 20)->nullable()->after('status');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore if already added concurrently
            }
        }

        $this->info("🚀 Seeding test audit data for Lead Quality & Executive Audit Board...");

        $admin = User::whereIn('role', ['super_admin', 'company_admin'])->first();
        $branchId = $admin?->branch_id;

        $exec1 = User::firstOrCreate(
            ['email' => 'chidinma.audit@bhl.com'],
            [
                'name' => 'Chidinma Okafor (Senior Executive)',
                'password' => Hash::make('password123'),
                'role' => 'sales_executive',
                'job_title' => 'Senior Sales Executive',
                'department' => 'Marketing & Sales',
                'phone_number' => '08033019842',
                'branch_id' => $branchId,
                'status' => 'active',
            ]
        );

        $exec2 = User::firstOrCreate(
            ['email' => 'tunde.audit@bhl.com'],
            [
                'name' => 'Tunde Bakare (Field Realtor)',
                'password' => Hash::make('password123'),
                'role' => 'sales_executive',
                'job_title' => 'Realtor & Acquisitions Agent',
                'department' => 'Marketing & Sales',
                'phone_number' => '08023194821',
                'branch_id' => $branchId,
                'status' => 'active',
            ]
        );

        $exec3 = User::firstOrCreate(
            ['email' => 'amina.audit@bhl.com'],
            [
                'name' => 'Amina Danjuma (Direct Sales)',
                'password' => Hash::make('password123'),
                'role' => 'sales_executive',
                'job_title' => 'Sales Representative',
                'department' => 'Marketing & Sales',
                'phone_number' => '08061234509',
                'branch_id' => $branchId,
                'status' => 'active',
            ]
        );

        // Clear previous demo leads
        $oldDemoIds = Lead::withoutGlobalScopes()->where('lead_source', 'like', '[Demo/Test]%')->pluck('id');
        LeadActivity::whereIn('lead_id', $oldDemoIds)->delete();
        Lead::withoutGlobalScopes()->whereIn('id', $oldDemoIds)->delete();

        $now = now();

        $demoLeads = [
            // Hot
            [
                'full_name' => 'Chief Adekunle Adeleke',
                'phone_number' => '08033019842',
                'whatsapp_number' => '08033019842',
                'email' => 'adekunle.demo@bhl.com',
                'budget_range' => '₦180M - ₦250M',
                'preferred_location' => 'Lekki Phase 1, Lagos',
                'outreach_location' => 'Victoria Island Office',
                'lead_source' => '[Demo/Test] Referral - Existing Client',
                'status' => 'Negotiation',
                'assigned_to' => $exec1->id,
                'notes' => 'High-net-worth investor. 4-bedroom detached duplex with BQ.',
                'created_at' => $now->copy()->subDays(2)->subHours(5),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(3),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Dr. Fatima Al-Hassan',
                'phone_number' => '08023194821',
                'whatsapp_number' => '08023194821',
                'email' => 'fatima.demo@bhl.com',
                'budget_range' => '₦120M - ₦160M',
                'preferred_location' => 'Guzape District, Abuja',
                'outreach_location' => 'Banex Plaza Roadshow',
                'lead_source' => '[Demo/Test] Google Search Ads',
                'status' => 'Payment Processing',
                'assigned_to' => $exec1->id,
                'notes' => 'Medical director. Making initial 30% milestone deposit on penthouse.',
                'created_at' => $now->copy()->subDays(3)->subHours(8),
                'last_contacted_at' => $now->copy()->subDays(3)->subHours(6),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Engr. Babatunde Fashina',
                'phone_number' => '08055123984',
                'whatsapp_number' => '08055123984',
                'email' => 'tunde.demo@bhl.com',
                'budget_range' => '₦65M - ₦90M',
                'preferred_location' => 'Epe Waterfront Corridor',
                'outreach_location' => 'Lekki Phase 1 Booth',
                'lead_source' => '[Demo/Test] Facebook Real Estate Campaign',
                'status' => 'Inspection Scheduled',
                'assigned_to' => $exec1->id,
                'notes' => 'Site inspection booked for Saturday 10:00 AM.',
                'created_at' => $now->copy()->subDays(1)->subHours(4),
                'last_contacted_at' => $now->copy()->subDays(1)->subHours(2),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Alhaji Ibrahim Danjuma',
                'phone_number' => '08061234509',
                'whatsapp_number' => '08061234509',
                'email' => 'danjuma.demo@bhl.com',
                'budget_range' => '₦200M - ₦350M',
                'preferred_location' => 'Maitama, Abuja',
                'outreach_location' => 'Transcorp Hilton Roadshow',
                'lead_source' => '[Demo/Test] Billboard - Airport Road',
                'status' => 'Negotiation',
                'assigned_to' => $exec2->id,
                'notes' => 'Commercial developer. Negotiating square meter land pricing.',
                'created_at' => $now->copy()->subDays(4)->subHours(9),
                'last_contacted_at' => $now->copy()->subDays(4)->subHours(6),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Senator (Mrs) Bisi Akintola',
                'phone_number' => '08034567890',
                'whatsapp_number' => '08034567890',
                'email' => 'bisi.demo@bhl.com',
                'budget_range' => '₦250M - ₦400M',
                'preferred_location' => 'Asokoro, Abuja',
                'outreach_location' => 'Abuja Airport Lounge',
                'lead_source' => '[Demo/Test] Direct Marketing',
                'status' => 'Negotiation',
                'assigned_to' => $exec1->id,
                'notes' => 'VIP investor. Luxury smart villa with private pool.',
                'created_at' => $now->copy()->subDays(2)->subHours(7),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(5),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Prince Adewale Adelekan',
                'phone_number' => '08028765432',
                'whatsapp_number' => '08028765432',
                'email' => 'adewale.demo@bhl.com',
                'budget_range' => '₦150M - ₦220M',
                'preferred_location' => 'Epe Waterfront Corridor',
                'outreach_location' => 'Lekki Tollgate Billboard',
                'lead_source' => '[Demo/Test] Billboard - Lekki-Epe Tollgate',
                'status' => 'Negotiation',
                'assigned_to' => $exec2->id,
                'notes' => 'Acquiring 5-acre waterfront tract for boutique resort.',
                'created_at' => $now->copy()->subDays(3)->subHours(10),
                'last_contacted_at' => $now->copy()->subDays(3)->subHours(7),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],

            // WhatsApp Only
            [
                'full_name' => 'Capt. Charles Emeka (UK Diaspora)',
                'phone_number' => '02079460192',
                'whatsapp_number' => '08139982145',
                'email' => 'charles.demo@bhl.com',
                'budget_range' => '₦95M - ₦130M',
                'preferred_location' => 'Ikoyi / Banana Island View',
                'outreach_location' => 'Diaspora Virtual Expo',
                'lead_source' => '[Demo/Test] Diaspora Virtual Expo',
                'status' => 'Contacted',
                'assigned_to' => $exec1->id,
                'notes' => 'Diaspora pilot. UK landline cannot be reached by local calls. WhatsApp only line.',
                'created_at' => $now->copy()->subDays(3)->subHours(4),
                'last_contacted_at' => $now->copy()->subDays(3)->subHours(2),
                'unreachable_count' => 1,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Mr. Somtochukwu Obi (US Diaspora)',
                'phone_number' => '01415555267',
                'whatsapp_number' => '08071239845',
                'email' => 'somto.demo@bhl.com',
                'budget_range' => '₦80M - ₦110M',
                'preferred_location' => 'Ibeju-Lekki (Dangote Refinery Zone)',
                'outreach_location' => 'Diaspora Virtual Expo',
                'lead_source' => '[Demo/Test] Instagram Sponsored Ad',
                'status' => 'Follow Up',
                'assigned_to' => $exec2->id,
                'notes' => 'US tech lead. Calls fail on local SIM. Engaged via WhatsApp.',
                'created_at' => $now->copy()->subDays(2)->subHours(6),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(3),
                'unreachable_count' => 1,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Dr. Ken Ekwueme (Canada)',
                'phone_number' => '01604555891',
                'whatsapp_number' => '08162234901',
                'email' => 'ken.demo@bhl.com',
                'budget_range' => '₦110M - ₦150M',
                'preferred_location' => 'Lekki Phase 1, Lagos',
                'outreach_location' => 'Web Webinar Registration',
                'lead_source' => '[Demo/Test] Google Search Ads',
                'status' => 'Contacted',
                'assigned_to' => $exec2->id,
                'notes' => 'Doctor in Calgary. Local phone uncallable. Communicates exclusively on WhatsApp.',
                'created_at' => $now->copy()->subDays(1)->subHours(5),
                'last_contacted_at' => $now->copy()->subDays(1)->subHours(2),
                'unreachable_count' => 2,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Mr. Austin Okeke (Houston)',
                'phone_number' => '01713555982',
                'whatsapp_number' => '08083344556',
                'email' => 'austin.demo@bhl.com',
                'budget_range' => '₦140M - ₦190M',
                'preferred_location' => 'Lekki Phase 1, Lagos',
                'outreach_location' => 'Diaspora Virtual Expo',
                'lead_source' => '[Demo/Test] Diaspora Virtual Expo',
                'status' => 'Follow Up',
                'assigned_to' => $exec1->id,
                'notes' => 'Oil & gas engineer in Houston. Phone unreachable. WhatsApp only.',
                'created_at' => $now->copy()->subDays(4)->subHours(5),
                'last_contacted_at' => $now->copy()->subDays(4)->subHours(1),
                'unreachable_count' => 1,
                'is_flagged_fake' => false,
            ],

            // Warm
            [
                'full_name' => 'Mrs. Ngozi Okonjo-Eze',
                'phone_number' => '08149021876',
                'whatsapp_number' => '08149021876',
                'email' => 'ngozi.demo@bhl.com',
                'budget_range' => '₦45M - ₦60M',
                'preferred_location' => 'Sangotedo / Monastery Road',
                'outreach_location' => 'Novare Mall Outreach',
                'lead_source' => '[Demo/Test] Instagram Sponsored Ad',
                'status' => 'Contacted',
                'assigned_to' => $exec1->id,
                'notes' => 'Corporate executive. Wants 2-bedroom terrace with 12-month payment plan.',
                'created_at' => $now->copy()->subDays(2)->subHours(8),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(4),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Barrister Zainab Mohammed',
                'phone_number' => '08099882211',
                'whatsapp_number' => '08099882211',
                'email' => 'zainab.demo@bhl.com',
                'budget_range' => '₦70M - ₦95M',
                'preferred_location' => 'Katampe Extension, Abuja',
                'outreach_location' => 'Abuja Trade Fair Stand',
                'lead_source' => '[Demo/Test] Referral - Existing Client',
                'status' => 'Follow Up',
                'assigned_to' => $exec1->id,
                'notes' => 'Legal practitioner. Reviewing layout survey and C of O documents.',
                'created_at' => $now->copy()->subDays(3)->subHours(6),
                'last_contacted_at' => $now->copy()->subDays(3)->subHours(3),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Hajia Amina Bello',
                'phone_number' => '08187654321',
                'whatsapp_number' => '08187654321',
                'email' => 'amina.demo@bhl.com',
                'budget_range' => '₦55M - ₦75M',
                'preferred_location' => 'Wuye District, Abuja',
                'outreach_location' => 'Abuja City Mall Stand',
                'lead_source' => '[Demo/Test] Facebook Real Estate Campaign',
                'status' => 'Contacted',
                'assigned_to' => $exec2->id,
                'notes' => 'Scheduled inspection for Friday with property manager.',
                'created_at' => $now->copy()->subDays(2)->subHours(9),
                'last_contacted_at' => $now->copy()->subDays(2)->subHours(5),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],

            // Cold & Dormant (for bulk reassignment test)
            [
                'full_name' => 'Pastor Samuel Olumide',
                'phone_number' => '08023456789',
                'whatsapp_number' => '08023456789',
                'email' => 'samuel.demo@bhl.com',
                'budget_range' => '₦35M - ₦50M',
                'preferred_location' => 'Ajah / Abraham Adesanya',
                'outreach_location' => 'Jubilee Bridge Roadshow',
                'lead_source' => '[Demo/Test] Daily Field Prospecting',
                'status' => 'New',
                'assigned_to' => $exec3->id,
                'notes' => 'Met at roadshow tent. Wants an off-plan 3-bedroom apartment. Uncontacted lead.',
                'created_at' => $now->copy()->subDays(5),
                'last_contacted_at' => null,
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Arch. David Nwachukwu',
                'phone_number' => '08039871234',
                'whatsapp_number' => '08039871234',
                'email' => 'david.demo@bhl.com',
                'budget_range' => '₦90M - ₦140M',
                'preferred_location' => 'GRA Ikeja, Lagos',
                'outreach_location' => 'Maryland Mall Roadshow',
                'lead_source' => '[Demo/Test] Walk-in Branch Enquiry',
                'status' => 'New',
                'assigned_to' => $exec3->id,
                'notes' => 'Architect looking for joint venture or prime land in Ikeja GRA. Needs callback.',
                'created_at' => $now->copy()->subDays(4),
                'last_contacted_at' => null,
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Mrs. Folashade Balogun',
                'phone_number' => '08101239876',
                'whatsapp_number' => '08101239876',
                'email' => 'folashade.demo@bhl.com',
                'budget_range' => '₦40M - ₦55M',
                'preferred_location' => 'Alagbado / Abule Egba Corridor',
                'outreach_location' => 'Ikeja City Mall Stand',
                'lead_source' => '[Demo/Test] Daily Field Prospecting',
                'status' => 'New',
                'assigned_to' => $exec3->id,
                'notes' => 'Branch manager seeking staff cooperative investment. Ideal for bulk reassignment.',
                'created_at' => $now->copy()->subDays(3),
                'last_contacted_at' => null,
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],

            // Flagged Fake
            [
                'full_name' => 'Spam Bot Test 01',
                'phone_number' => '08000000000',
                'whatsapp_number' => '08000000000',
                'email' => 'spambot1@fakelead.xyz',
                'budget_range' => '₦10M - ₦15M',
                'preferred_location' => 'Unknown',
                'outreach_location' => 'Online Form Injection',
                'lead_source' => '[Demo/Test] Facebook Real Estate Campaign',
                'status' => 'New',
                'assigned_to' => $exec3->id,
                'notes' => 'Suspicious bot submission. Repeated digits, phone dialer unreachable.',
                'created_at' => $now->copy()->subDays(2),
                'last_contacted_at' => null,
                'unreachable_count' => 3,
                'is_flagged_fake' => true,
                'flagged_reason' => 'Repeated invalid digits pattern / unreachable phone line.',
                'flagged_at' => $now->copy()->subDays(1),
            ],

            // Closed Won
            [
                'full_name' => 'Mrs. Blessing Kalu',
                'phone_number' => '08123456701',
                'whatsapp_number' => '08123456701',
                'email' => 'blessing.demo@bhl.com',
                'budget_range' => '₦75M - ₦100M',
                'preferred_location' => 'Sangotedo / Monastery Road',
                'outreach_location' => 'Lekki Conservation Walkway',
                'lead_source' => '[Demo/Test] Facebook Real Estate Campaign',
                'status' => 'Closed Won',
                'assigned_to' => $exec1->id,
                'notes' => 'Deal closed! Purchased 4-bedroom terrace duplex.',
                'created_at' => $now->copy()->subDays(5)->subHours(6),
                'last_contacted_at' => $now->copy()->subDays(5)->subHours(3),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
            [
                'full_name' => 'Chief Emeka Ofor',
                'phone_number' => '08027788991',
                'whatsapp_number' => '08027788991',
                'email' => 'emeka.demo@bhl.com',
                'budget_range' => '₦210M - ₦300M',
                'preferred_location' => 'Guzape District, Abuja',
                'outreach_location' => 'Transcorp Hilton Roadshow',
                'lead_source' => '[Demo/Test] Referral - Existing Client',
                'status' => 'Closed Won',
                'assigned_to' => $exec1->id,
                'notes' => 'Deal closed! Paid in full for 5-bedroom luxury smart villa in Guzape.',
                'created_at' => $now->copy()->subDays(6)->subHours(4),
                'last_contacted_at' => $now->copy()->subDays(6)->subHours(1),
                'unreachable_count' => 0,
                'is_flagged_fake' => false,
            ],
        ];

        $count = 0;
        foreach ($demoLeads as $data) {
            $data['branch_id'] = $branchId;
            $lead = Lead::create($data);
            $count++;

            if (!empty($data['last_contacted_at'])) {
                LeadActivity::create([
                    'lead_id' => $lead->id,
                    'user_id' => $data['assigned_to'],
                    'activity_type' => 'Call Logged',
                    'description' => "Initial discovery consultation logged. Status: {$data['status']}.",
                    'created_at' => $data['last_contacted_at'],
                ]);
            }
        }

        $this->info("✅ Successfully generated {$count} test audit leads across 3 sales executives.");
        return;
    }
}
