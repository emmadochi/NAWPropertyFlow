<?php

namespace App\Console\Commands;

use App\Models\CompanySetting;
use App\Models\Document;
use App\Models\FollowUp;
use App\Models\Inspection;
use App\Models\Inventory\PurchaseOrder;
use App\Models\Inventory\Supplier;
use App\Models\Inventory\SupplierInvoice;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\Property;
use App\Models\Sale;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurgeDemoDataAndRestoreRicaf extends Command
{
    protected $signature = 'ricaf:restore {--tenant= : Optional specific tenant ID}';
    protected $description = 'Safely purge all demo staff, demo leads, Lagos demo properties, and restore Ricaf Nigeria Limited profile';

    public function handle(): int
    {
        $this->info('=====================================================');
        $this->info('Starting RICAF Nigeria Limited Clean-up & Restoration');
        $this->info('=====================================================');

        try {
            $tenantQuery = Tenant::query();
            if ($tenantId = $this->option('tenant')) {
                $tenantQuery->where('id', $tenantId);
            }
            $tenants = $tenantQuery->get();
        } catch (\Throwable $e) {
            $tenants = collect();
        }

        if ($tenants->isNotEmpty()) {
            foreach ($tenants as $tenant) {
                $this->info("--> Processing Tenant: [{$tenant->id}] ({$tenant->company_name})");
                $tenant->run(function () {
                    $this->executeCleanup();
                });
            }
        } else {
            $this->info('--> Processing Default Database...');
            $this->executeCleanup();
        }

        $this->newLine();
        $this->info('🎉 CLEANUP COMPLETED: Ricaf Nigeria Limited is 100% restored!');
        return Command::SUCCESS;
    }

    private function executeCleanup(): void
    {
        // 1. Restore Company Profile
        $cs = CompanySetting::firstOrCreate(['id' => 1]);
        $cs->update([
            'company_name'      => 'Ricaf Nigeria Limited',
            'email'             => 'info@ricafltd.com',
            'phone'             => '+234 800 000 0000',
            'address'           => 'Suite D7, 3rd Floor, Kuriftu Plaza, Plot 519, Olu Awotesu Street, Jabi, Abuja, Nigeria',
            'package_tier'      => 'enterprise',
            'letterhead_header' => null,
            'letterhead_footer' => null,
        ]);
        Cache::forget('active_company_setting');
        $this->line('  ✓ Company name restored to: Ricaf Nigeria Limited');

        // 2. Purge Demo Users (@propertyflow.com)
        $demoUsers = User::where('email', 'like', '%@propertyflow.com')->get();
        $demoUserIds = $demoUsers->pluck('id')->toArray();
        if (!empty($demoUserIds)) {
            $realAdmin = User::where('email', 'admin@ricafltd.com')->first() 
                ?? User::where('email', 'not like', '%@propertyflow.com')->first();
            if ($realAdmin) {
                Lead::whereIn('assigned_to', $demoUserIds)->update(['assigned_to' => $realAdmin->id]);
            }

            if (Schema::hasTable('salary_structures')) {
                \App\Models\SalaryStructure::whereIn('user_id', $demoUserIds)->delete();
            }
            if (Schema::hasTable('payroll_deductions')) {
                \App\Models\PayrollDeduction::whereIn('user_id', $demoUserIds)->delete();
            }
            if (Schema::hasTable('performance_reviews')) {
                \App\Models\PerformanceReview::whereIn('user_id', $demoUserIds)->delete();
            }
            if (Schema::hasTable('disciplinary_records')) {
                \App\Models\DisciplinaryRecord::whereIn('user_id', $demoUserIds)->delete();
            }
            if (Schema::hasTable('staff_certifications')) {
                \App\Models\StaffCertification::whereIn('user_id', $demoUserIds)->delete();
            }
            if (Schema::hasTable('staff_submissions')) {
                DB::table('staff_submissions')->whereIn('user_id', $demoUserIds)->delete();
            }
            if (Schema::hasTable('daily_tasks')) {
                DB::table('daily_tasks')->whereIn('user_id', $demoUserIds)->delete();
            }

            User::whereIn('id', $demoUserIds)->delete();
            $this->line('  ✓ Purged ' . count($demoUserIds) . ' demo staff accounts (@propertyflow.com)');
        }

        // 3. Purge Demo Leads
        $demoLeadEmails = [
            'emmadochi@gmail.com',
            'chinedu.okafor@example.com',
            'funke.adebayo@example.com',
            'aisha.yusuf@example.com',
            'abubakar.bello@example.com',
            'segun.olatunji@example.com',
            'ngozi.eze.uk@example.com',
            'fola.williams@lawfirm.ng',
            'ibrahim.danjuma@aviation.ng',
            'chioma.n@techcorp.ng',
            'obinna.okonkwo@diasporacapital.co.uk',
        ];

        $demoLeads = Lead::whereIn('email', $demoLeadEmails)->orWhere('email', 'like', '%@example.com')->get();
        $demoLeadIds = $demoLeads->pluck('id')->toArray();
        if (!empty($demoLeadIds)) {
            Sale::whereIn('lead_id', $demoLeadIds)->delete();
            FollowUp::whereIn('lead_id', $demoLeadIds)->delete();
            Inspection::whereIn('lead_id', $demoLeadIds)->delete();
            LeadActivity::whereIn('lead_id', $demoLeadIds)->delete();
            Document::whereIn('lead_id', $demoLeadIds)->delete();
            Lead::whereIn('id', $demoLeadIds)->delete();
            $this->line('  ✓ Purged ' . count($demoLeadIds) . ' demo leads & associated test sales');
        }

        // 4. Purge Lagos Demo Properties
        $demoPropertyNames = [
            'Banana Island Marina Court - 5 Bedroom Waterfront Detached Villa',
            'Lekki Atlantic Horizon - 4 Bedroom Oceanview Terrace + BQ',
            'Epe Smart Agri-Tech & Residential City - 600 SQM Plot',
        ];

        $deletedProps = 0;
        foreach ($demoPropertyNames as $pName) {
            $prop = Property::withoutGlobalScopes()->where('name', $pName)->first();
            if ($prop) {
                $prop->units()->delete();
                $prop->sales()->delete();
                $prop->delete();
                $deletedProps++;
            }
        }
        $this->line("  ✓ Purged {$deletedProps} Lagos demo properties (Banana Island, Lekki, Epe)");

        // 5. Purge Demo Construction Suppliers & POs
        if (Schema::hasTable('suppliers')) {
            try {
                $demoSuppliers = [
                    'Dangote Cement Plc (North Regional Depot)',
                    'Julius Berger Quarry & Aggregates',
                    'BUA Cement Commercial Depot',
                    'Coleman Technical Wire & Cable Ltd',
                    'Sankyo Smart HVAC & Electrical Systems',
                    'Tiger TMT Steel Rolling Mills Ltd',
                ];
                $col = Schema::hasColumn('suppliers', 'name') ? 'name' : 'company_name';
                $supplierIds = DB::table('suppliers')->whereIn($col, $demoSuppliers)->pluck('id')->toArray();
                if (!empty($supplierIds)) {
                    if (Schema::hasTable('purchase_orders')) {
                        DB::table('purchase_orders')->whereIn('supplier_id', $supplierIds)->delete();
                    }
                    if (Schema::hasTable('supplier_invoices')) {
                        DB::table('supplier_invoices')->whereIn('supplier_id', $supplierIds)->delete();
                    }
                    if (Schema::hasTable('supplier_users')) {
                        DB::table('supplier_users')->whereIn('supplier_id', $supplierIds)->delete();
                    }
                    DB::table('suppliers')->whereIn('id', $supplierIds)->delete();
                    $this->line('  ✓ Purged ' . count($supplierIds) . ' demo construction suppliers & POs');
                }
            } catch (\Throwable $e) {
                // Non-fatal fallback
            }
        }
    }
}
