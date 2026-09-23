<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PropertyUnitController;
use App\Http\Controllers\InspectionController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\DocumentTemplateController;
use App\Http\Controllers\GeneratedDocumentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CompanySettingController;
use App\Http\Controllers\HRController;
use App\Http\Controllers\StaffProfileController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\DripSequenceController;
use App\Http\Controllers\LeadQualityAuditController;
use App\Http\Controllers\DepartmentTargetController;
use App\Http\Controllers\DepartmentReportController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\StaffSubmissionController;
use App\Http\Controllers\BuyerDashboardController;
use App\Http\Controllers\FileStorageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| All CRM routes live here. They are only accessible via a tenant subdomain
| (e.g., clientname.localhost). The InitializeTenancyBySubdomain middleware
| automatically switches the database connection to the tenant's database.
|
*/

Route::middleware(array_values(array_filter([
    'web',
    \App\Providers\TenancyServiceProvider::isTenancyActive() ? InitializeTenancyBySubdomain::class : null,
])))->group(function () {

    // Landing redirect (Show landing page on main domain, CRM login on demo / tenant subdomains)
    Route::get('/', function () {
        $host = request()->getHost();
        if (in_array($host, ['nawpropertyflow.com.ng', 'www.nawpropertyflow.com.ng'])) {
            return view('welcome');
        }
        return redirect()->route('login');
    })->name('home');

    Route::get('/landing', function () {
        return view('welcome');
    })->name('landing');

    // Dynamic Multi-Tenant PWA Manifest & App Icons
    Route::get('/manifest.json', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('tenant.manifest');
    Route::get('/manifest.webmanifest', [\App\Http\Controllers\PwaController::class, 'manifest'])->name('tenant.webmanifest');
    Route::get('/pwa-icon/{size?}', [\App\Http\Controllers\PwaController::class, 'icon'])->name('tenant.pwa.icon');

    // Guaranteed static company assets route (bypasses tenancy prefix and missing symlinks)
    Route::get('/company/{file}', function ($file) {
        if (str_contains($file, 'buckcrest-crest')) {
            return response(\App\Support\BuckcrestAssets::crestPngBinary(), 200, [
                'Content-Type'  => 'image/png',
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
        $candidates = [
            public_path("company/{$file}"),
            public_path("storage/company/{$file}"),
            storage_path("app/public/company/{$file}"),
        ];
        foreach ($candidates as $path) {
            if (file_exists($path) && is_readable($path)) {
                $mime = mime_content_type($path) ?: 'image/png';
                return response(file_get_contents($path), 200, [
                    'Content-Type'  => $mime,
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }
        abort(404);
    })->name('tenant.company.asset');

    // One-click migration runner (run pending tenant migrations without SSH)
    Route::get('/run-migrations', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', [
                '--path'  => 'database/migrations/tenant',
                '--force' => true,
            ]);
            $output = \Illuminate\Support\Facades\Artisan::output();
            return response('<pre style="font-family:monospace;background:#0f172a;color:#86efac;padding:2rem;border-radius:1rem;font-size:14px;"><strong style="color:#fbbf24;">✅ Migrations ran successfully:</strong>' . "\n\n" . htmlspecialchars($output) . '</pre>', 200, ['Content-Type' => 'text/html']);
        } catch (\Throwable $e) {
            return response('<pre style="font-family:monospace;background:#450a0a;color:#fca5a5;padding:2rem;border-radius:1rem;font-size:14px;"><strong>❌ Migration Error:</strong>' . "\n\n" . htmlspecialchars($e->getMessage()) . '</pre>', 500, ['Content-Type' => 'text/html']);
        }
    })->name('run-migrations');

    // One-click comprehensive demo purge & Ricaf profile restoration
    Route::get('/restore-ricaf-defaults', function () {
        // Allow if user is authenticated admin OR if secret key is provided
        $isAdmin = \Illuminate\Support\Facades\Auth::check() && in_array(\Illuminate\Support\Facades\Auth::user()->role, ['super_admin', 'company_admin']);
        $hasKey = request()->query('key') === 'ricaf2026cleanup';

        if (!$isAdmin && !$hasKey) {
            abort(403, 'Unauthorized. Please log in as Super Admin or supply the restoration key.');
        }

        $log = [];

        // 1. Restore Company Settings to Ricaf Nigeria Limited
        $cs = \App\Models\CompanySetting::firstOrCreate(['id' => 1]);
        $cs->update([
            'company_name'      => 'Ricaf Nigeria Limited',
            'email'             => 'info@ricafltd.com',
            'phone'             => '+234 800 000 0000',
            'address'           => 'Suite D7, 3rd Floor, Kuriftu Plaza, Plot 519, Olu Awotesu Street, Jabi, Abuja, Nigeria',
            'package_tier'      => 'enterprise',
            'letterhead_header' => null,
            'letterhead_footer' => null,
        ]);
        \Illuminate\Support\Facades\Cache::forget('active_company_setting');
        $log[] = '✅ Company Profile restored to: <strong>Ricaf Nigeria Limited</strong> (Cache cleared).';

        // 2. Identify and Purge Demo Staff (@propertyflow.com)
        $demoUsers = \App\Models\User::where('email', 'like', '%@propertyflow.com')->get();
        $demoUserIds = $demoUsers->pluck('id')->toArray();
        $demoUserCount = count($demoUserIds);

        if ($demoUserCount > 0) {
            // Find a safe real admin to reassign any genuine leads if needed
            $realAdmin = \App\Models\User::where('email', 'admin@ricafltd.com')->first() 
                ?? \App\Models\User::where('email', 'not like', '%@propertyflow.com')->first();
            $fallbackAdminId = $realAdmin ? $realAdmin->id : null;

            if ($fallbackAdminId) {
                \App\Models\Lead::whereIn('assigned_to', $demoUserIds)->update(['assigned_to' => $fallbackAdminId]);
            }

            // Remove associated HR/Payroll demo records
            if (\Illuminate\Support\Facades\Schema::hasTable('salary_structures')) {
                \App\Models\SalaryStructure::whereIn('user_id', $demoUserIds)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('payroll_deductions')) {
                \App\Models\PayrollDeduction::whereIn('user_id', $demoUserIds)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('performance_reviews')) {
                \App\Models\PerformanceReview::whereIn('user_id', $demoUserIds)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('disciplinary_records')) {
                \App\Models\DisciplinaryRecord::whereIn('user_id', $demoUserIds)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('staff_certifications')) {
                \App\Models\StaffCertification::whereIn('user_id', $demoUserIds)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('staff_submissions')) {
                \Illuminate\Support\Facades\DB::table('staff_submissions')->whereIn('user_id', $demoUserIds)->delete();
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('daily_tasks')) {
                \Illuminate\Support\Facades\DB::table('daily_tasks')->whereIn('user_id', $demoUserIds)->delete();
            }

            \App\Models\User::whereIn('id', $demoUserIds)->delete();
            $log[] = "✅ Purged {$demoUserCount} demo staff accounts (@propertyflow.com).";
        } else {
            $log[] = "ℹ️ No @propertyflow.com demo staff found.";
        }

        // 3. Identify and Purge Demo Leads
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

        $demoLeads = \App\Models\Lead::whereIn('email', $demoLeadEmails)
            ->orWhere('email', 'like', '%@example.com')
            ->get();
        $demoLeadIds = $demoLeads->pluck('id')->toArray();
        $demoLeadCount = count($demoLeadIds);

        if ($demoLeadCount > 0) {
            \App\Models\Sale::whereIn('lead_id', $demoLeadIds)->delete();
            \App\Models\FollowUp::whereIn('lead_id', $demoLeadIds)->delete();
            \App\Models\Inspection::whereIn('lead_id', $demoLeadIds)->delete();
            \App\Models\LeadActivity::whereIn('lead_id', $demoLeadIds)->delete();
            \App\Models\Document::whereIn('lead_id', $demoLeadIds)->delete();
            \App\Models\Lead::whereIn('id', $demoLeadIds)->delete();
            $log[] = "✅ Purged {$demoLeadCount} demo leads (Chinedu Okafor, Funke Adebayo, Obinna Okonkwo, etc.) and associated test sales.";
        } else {
            $log[] = "ℹ️ No demo leads found.";
        }

        // 4. Safely Purge Lagos Demo Properties
        $demoPropertyNames = [
            'Banana Island Marina Court - 5 Bedroom Waterfront Detached Villa',
            'Lekki Atlantic Horizon - 4 Bedroom Oceanview Terrace + BQ',
            'Epe Smart Agri-Tech & Residential City - 600 SQM Plot',
        ];

        $deletedProps = 0;
        foreach ($demoPropertyNames as $pName) {
            $prop = \App\Models\Property::withoutGlobalScopes()->where('name', $pName)->first();
            if ($prop) {
                // Delete associated units
                $prop->units()->delete();
                $prop->sales()->delete();
                $prop->delete();
                $deletedProps++;
            }
        }
        $log[] = "✅ Purged {$deletedProps} Lagos demo properties (Banana Island, Lekki, Epe).";

        // 5. Purge Demo Construction Inventory & Demo Suppliers
        if (\Illuminate\Support\Facades\Schema::hasTable('suppliers')) {
            try {
                $demoSuppliers = [
                    'Dangote Cement Plc (North Regional Depot)',
                    'Julius Berger Quarry & Aggregates',
                    'BUA Cement Commercial Depot',
                    'Coleman Technical Wire & Cable Ltd',
                    'Sankyo Smart HVAC & Electrical Systems',
                    'Tiger TMT Steel Rolling Mills Ltd',
                ];
                $col = \Illuminate\Support\Facades\Schema::hasColumn('suppliers', 'name') ? 'name' : 'company_name';
                $supplierIds = \Illuminate\Support\Facades\DB::table('suppliers')->whereIn($col, $demoSuppliers)->pluck('id')->toArray();
                if (!empty($supplierIds)) {
                    if (\Illuminate\Support\Facades\Schema::hasTable('purchase_orders')) {
                        \Illuminate\Support\Facades\DB::table('purchase_orders')->whereIn('supplier_id', $supplierIds)->delete();
                    }
                    if (\Illuminate\Support\Facades\Schema::hasTable('supplier_invoices')) {
                        \Illuminate\Support\Facades\DB::table('supplier_invoices')->whereIn('supplier_id', $supplierIds)->delete();
                    }
                    if (\Illuminate\Support\Facades\Schema::hasTable('supplier_users')) {
                        \Illuminate\Support\Facades\DB::table('supplier_users')->whereIn('supplier_id', $supplierIds)->delete();
                    }
                    \Illuminate\Support\Facades\DB::table('suppliers')->whereIn('id', $supplierIds)->delete();
                    $log[] = "✅ Purged " . count($supplierIds) . " demo construction suppliers and demo purchase orders.";
                }
            } catch (\Throwable $e) {
                // Non-fatal
            }
        }

        $logHtml = implode('<br>', $log);

        return response('
        <!DOCTYPE html>
        <html>
        <head>
            <title>Ricaf Database Cleaned & Restored</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; padding: 2rem; display: flex; justify-content: center; }
                .card { background: #1e293b; border: 1px solid #334155; border-radius: 1.5rem; max-width: 680px; width: 100%; padding: 2.5rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
                .badge { background: #10b981; color: #fff; padding: 0.35rem 0.85rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 800; display: inline-block; margin-bottom: 1.25rem; }
                h1 { margin: 0 0 0.75rem 0; font-size: 1.75rem; color: #fff; }
                p { color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem; }
                .log-box { background: #0f172a; border: 1px solid #334155; border-radius: 1rem; padding: 1.25rem; font-size: 0.9rem; line-height: 1.8; color: #e2e8f0; margin-bottom: 2rem; }
                .btn { display: inline-block; text-align: center; background: #f97316; color: #fff; padding: 0.9rem 2rem; border-radius: 0.75rem; font-weight: bold; text-decoration: none; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3); }
                .btn:hover { background: #ea580c; }
            </style>
        </head>
        <body>
            <div class="card">
                <span class="badge">✓ RESTORATION COMPLETED</span>
                <h1>Ricaf Nigeria Limited Restored!</h1>
                <p>All injected demo staff, demo leads, Lagos listings, and test records have been successfully purged from your database. Your authentic company profile has been restored.</p>
                
                <div class="log-box">
                    ' . $logHtml . '
                </div>

                <a href="/dashboard" class="btn">Return to CRM Dashboard →</a>
            </div>
        </body>
        </html>
        ', 200, ['Content-Type' => 'text/html']);
    })->name('restore-ricaf');



    // Campaign Tracking (public, within tenant context)
    Route::get('campaigns/track/open/{token}', [CampaignController::class, 'trackOpen'])->name('campaigns.track.open');
    Route::get('campaigns/track/click/{token}', [CampaignController::class, 'trackClick'])->name('campaigns.track.click');

    // Customer Magic Link Authentication (Rate limited for security)
    Route::get('portal/access/{token}', [BuyerDashboardController::class, 'magicLogin'])
        ->middleware(['throttle:30,1'])
        ->name('portal.magic-login');

    // Guest Routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'handleForgotPassword'])->name('password.email');
        Route::get('/reset-password', [AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'updatePasswordWithToken'])->name('password.update');
    });

    // Authenticated Routes
    Route::middleware(['auth'])->group(function () {
        Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('/change-password', [AuthController::class, 'changePassword'])->name('password.change');

        // CSRF Token Refresh — used by AJAX forms to prevent 419 on long-lived sessions
        Route::get('/csrf-refresh', function () {
            return response()->json(['token' => csrf_token()]);
        })->name('csrf.refresh');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Virtual Tour & Interactive Estate Map
        Route::get('/virtual-tour', function () {
            $properties = \App\Models\Property::with(['units' => function($q) {
                $q->orderBy('unit_number', 'asc');
            }])->orderBy('name', 'asc')->get();

            return view('virtual-tour', compact('properties'));
        })->name('virtual-tour');


        // Leads
        Route::get('leads/import/template', [LeadController::class, 'importTemplate'])->name('leads.import-template');
        Route::post('leads/import', [LeadController::class, 'import'])->name('leads.import');

        // Lead Quality & Executive Audit Board — must be before resource to avoid {lead} conflict
        Route::get('leads/quality-audit', [LeadQualityAuditController::class, 'index'])->name('leads.quality-audit');
        Route::get('leads/quality-audit/export', [LeadQualityAuditController::class, 'export'])->name('leads.quality-audit.export');
        Route::post('leads/bulk-reassign', [LeadQualityAuditController::class, 'bulkReassign'])->name('leads.bulk-reassign');
        Route::patch('leads/{lead}/clear-flag', [LeadQualityAuditController::class, 'clearFlag'])->name('leads.clear-flag');
        Route::patch('leads/{lead}/reassign', [LeadQualityAuditController::class, 'reassign'])->name('leads.reassign');
        Route::patch('leads/{lead}/temperature', [LeadQualityAuditController::class, 'setTemperature'])->name('leads.set-temperature');

        Route::resource('leads', LeadController::class);
        Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
        Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
        Route::post('leads/{lead}/notes', [LeadController::class, 'storeNote'])->name('leads.notes.store');
        Route::post('leads/{lead}/log-click', [\App\Http\Controllers\ActivityQuickLogController::class, 'logClick'])->name('leads.log-click');
        Route::post('leads/{lead}/log-outcome', [\App\Http\Controllers\ActivityQuickLogController::class, 'logOutcome'])->name('leads.log-outcome');
        Route::post('leads/daily-pulse', [\App\Http\Controllers\ActivityQuickLogController::class, 'dailyPulse'])->name('leads.daily-pulse');

        // Properties
        Route::resource('properties', PropertyController::class);

        // Property Units
        Route::prefix('properties/{property}/units')->name('properties.units.')->group(function () {
            Route::get('/', [PropertyUnitController::class, 'index'])->name('index');
            Route::get('/create', [PropertyUnitController::class, 'create'])->name('create');
            Route::post('/', [PropertyUnitController::class, 'store'])->name('store');
            Route::get('/{unit}/edit', [PropertyUnitController::class, 'edit'])->name('edit');
            Route::put('/{unit}', [PropertyUnitController::class, 'update'])->name('update');
            Route::delete('/{unit}', [PropertyUnitController::class, 'destroy'])->name('destroy');
            Route::post('/{unit}/reserve', [PropertyUnitController::class, 'reserve'])->name('reserve');
            Route::post('/{unit}/release', [PropertyUnitController::class, 'release'])->name('release');
            Route::post('/{unit}/convert-sale', [PropertyUnitController::class, 'convertReservedToSale'])->name('convert-sale');
            Route::post('/bulk-create', [PropertyUnitController::class, 'bulkCreate'])->name('bulk-create');
        });

        // Projects & Milestones
        Route::resource('projects', ProjectController::class);
        Route::post('projects/{project}/milestones', [ProjectController::class, 'storeMilestone'])->name('projects.milestones.store');
        Route::put('projects/{project}/milestones/{milestone}', [ProjectController::class, 'updateMilestone'])->name('projects.milestones.update');
        Route::delete('projects/{project}/milestones/{milestone}', [ProjectController::class, 'destroyMilestone'])->name('projects.milestones.destroy');

        // Inspections & Follow-Ups
        Route::resource('inspections', InspectionController::class);
        Route::resource('follow-ups', FollowUpController::class);

        // Sales
        Route::post('sales', [SaleController::class, 'store'])->name('sales.store');

        // Documents
        Route::middleware(['feature:docs'])->group(function () {
            Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
            Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
            Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

            Route::middleware(['permission:properties.view,sales.record'])->group(function () {
                Route::get('generated-documents', [GeneratedDocumentController::class, 'index'])->name('generated-documents.index');
                Route::get('generated-documents/{document}', [GeneratedDocumentController::class, 'show'])->name('generated-documents.show');
                Route::get('generated-documents/{document}/download', [GeneratedDocumentController::class, 'download'])->name('generated-documents.download');
                Route::post('generated-documents/{document}/email', [GeneratedDocumentController::class, 'email'])->name('generated-documents.email');
            });

            Route::middleware(['role:super_admin,company_admin'])->group(function () {
                Route::resource('document-templates', DocumentTemplateController::class);
                Route::post('generated-documents/generate', [GeneratedDocumentController::class, 'generate'])->name('generated-documents.generate');
            });
        });

        // Payments & Milestones
        Route::get('sales/{sale}/payment-plan/create', [PaymentController::class, 'createPlan'])->name('payments.create-plan');
        Route::post('sales/{sale}/payment-plan', [PaymentController::class, 'storePlan'])->name('payments.store-plan');
        Route::get('payments/{paymentPlan}/milestones', [PaymentController::class, 'showPlan'])->name('payments.show-plan');
        Route::post('payments/milestones/{milestone}/payments', [PaymentController::class, 'recordPayment'])->name('payments.record-payment');
        Route::post('payments/milestones/{milestone}/verify', [PaymentController::class, 'verifyPayment'])->name('payments.verify-payment');
        Route::get('payments/milestones/{milestone}/receipt', [PaymentController::class, 'downloadReceipt'])->name('payments.download-receipt');

        // Team Management
        Route::middleware(['role:super_admin,company_admin,hr'])->group(function () {
            Route::get('settings', [UserController::class, 'index'])->name('settings.index');
            Route::post('settings/users', [UserController::class, 'store'])->name('settings.users.store');
            Route::put('settings/users/{user}', [UserController::class, 'update'])->name('settings.users.update');
            Route::delete('settings/users/{user}', [UserController::class, 'destroy'])->name('settings.users.destroy');
        });

        // Activity Logs & Admin Settings
        Route::middleware(['role:super_admin,company_admin'])->group(function () {
            Route::get('settings/activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('settings.activity-logs.index');
            Route::resource('branches', BranchController::class)->except(['create', 'show', 'edit']);
            Route::get('settings/company', [CompanySettingController::class, 'edit'])->name('settings.company.edit');
            Route::put('settings/company', [CompanySettingController::class, 'update'])->name('settings.company.update');
            Route::get('settings/departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::post('settings/departments', [DepartmentController::class, 'store'])->name('departments.store');
            Route::put('settings/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
            Route::patch('settings/departments/{department}/toggle', [DepartmentController::class, 'toggle'])->name('departments.toggle');
            Route::post('settings/departments/{department}/metrics', [DepartmentController::class, 'storeMetric'])->name('departments.metrics.store');
            Route::patch('settings/departments/metrics/{metric}/toggle', [DepartmentController::class, 'toggleMetric'])->name('departments.metrics.toggle');

            // Payment Plan Durations & Interest Rates
            Route::get('settings/payment-plans', [\App\Http\Controllers\PaymentPlanDurationController::class, 'index'])->name('settings.payment-plans.index');
            Route::post('settings/payment-plans', [\App\Http\Controllers\PaymentPlanDurationController::class, 'store'])->name('settings.payment-plans.store');
            Route::put('settings/payment-plans/{paymentPlan}', [\App\Http\Controllers\PaymentPlanDurationController::class, 'update'])->name('settings.payment-plans.update');
            Route::post('settings/payment-plans/{paymentPlan}/toggle', [\App\Http\Controllers\PaymentPlanDurationController::class, 'toggle'])->name('settings.payment-plans.toggle');
            Route::delete('settings/payment-plans/{paymentPlan}', [\App\Http\Controllers\PaymentPlanDurationController::class, 'destroy'])->name('settings.payment-plans.destroy');

            // Developer Master Module Switchboard
            Route::get('developer/modules', [\App\Http\Controllers\DeveloperModuleController::class, 'index'])->name('developer.modules.index');
            Route::post('developer/modules', [\App\Http\Controllers\DeveloperModuleController::class, 'update'])->name('developer.modules.update');
            Route::post('developer/modules/reset', [\App\Http\Controllers\DeveloperModuleController::class, 'resetToTier'])->name('developer.modules.reset');
        });

        // Retail Sales Performance Scorecard (Weekly / Monthly)
        Route::get('reports/retail-performance', [\App\Http\Controllers\RetailPerformanceController::class, 'index'])->name('reports.retail.index');
        Route::get('reports/retail-performance/export', [\App\Http\Controllers\RetailPerformanceController::class, 'export'])->name('reports.retail.export');

        // Reports
        Route::middleware(['permission:finance.view_ledger,hr.manage_targets', 'feature:advanced_reports'])->group(function () {
            Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
            Route::get('reports/departments', [DepartmentReportController::class, 'index'])->name('reports.departments.index');
            Route::get('reports/export/leads', [ReportController::class, 'exportLeads'])->name('reports.export.leads');
            Route::get('reports/export/sales', [ReportController::class, 'exportSales'])->name('reports.export.sales');
            Route::get('reports/export/leads-by-source', [ReportController::class, 'exportLeadsBySource'])->name('reports.export.leads-by-source');
            Route::get('reports/export/sales-by-agent', [ReportController::class, 'exportSalesByAgent'])->name('reports.export.sales-by-agent');
            Route::get('reports/export/followup-compliance', [ReportController::class, 'exportFollowUpCompliance'])->name('reports.export.followup-compliance');
            Route::get('reports/export/branch-comparison', [ReportController::class, 'exportBranchComparison'])->name('reports.export.branch-comparison');
        });

        // Marketing & Campaigns
        Route::middleware(['permission:marketing.view,marketing.send_broadcast,marketing.manage_drip', 'feature:marketing'])->group(function () {
            Route::get('campaigns/analytics', [CampaignController::class, 'analyticsOverview'])->name('campaigns.analytics');
            Route::resource('campaigns', CampaignController::class)->except(['edit', 'update']);
            Route::post('campaigns/upload-image', [CampaignController::class, 'uploadImage'])->name('campaigns.upload-image');
            Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
            Route::post('campaigns/preview-audience', [CampaignController::class, 'previewAudience'])->name('campaigns.preview-audience');
            Route::post('campaigns/send-test', [CampaignController::class, 'sendTest'])->name('campaigns.send-test');
            Route::resource('drip-sequences', DripSequenceController::class);
            Route::patch('drip-sequences/{drip_sequence}/toggle', [DripSequenceController::class, 'toggle'])->name('drip-sequences.toggle');
            Route::post('drip-sequences/{drip_sequence}/steps', [DripSequenceController::class, 'addStep'])->name('drip-sequences.steps.store');
            Route::put('drip-sequences/{drip_sequence}/steps/{drip_step}', [DripSequenceController::class, 'updateStep'])->name('drip-sequences.steps.update');
            Route::delete('drip-sequences/{drip_sequence}/steps/{drip_step}', [DripSequenceController::class, 'deleteStep'])->name('drip-sequences.steps.destroy');
        });

        // HR & Performance Management
        Route::middleware(['feature:hr'])->group(function () {
            Route::get('hr/leave', [HRController::class, 'leaveIndex'])->name('hr.leave.index');
            Route::get('hr/leave/create', [HRController::class, 'leaveCreate'])->name('hr.leave.create');
            Route::post('hr/leave', [HRController::class, 'leaveStore'])->name('hr.leave.store');
            Route::patch('hr/leave/{leave}/review', [HRController::class, 'leaveReview'])->name('hr.leave.review');
            Route::get('hr/my-submissions', [StaffSubmissionController::class, 'index'])->name('hr.submissions.index');
            Route::post('hr/my-submissions', [StaffSubmissionController::class, 'store'])->name('hr.submissions.store');
            Route::get('hr/submissions-review', [StaffSubmissionController::class, 'hodIndex'])->name('hr.submissions.review');
            Route::post('hr/submissions-review/{submission}/approve', [StaffSubmissionController::class, 'approve'])->name('hr.submissions.approve');
            Route::post('hr/submissions-review/{submission}/reject', [StaffSubmissionController::class, 'reject'])->name('hr.submissions.reject');
            Route::get('hr/department-targets', [DepartmentTargetController::class, 'index'])->name('hr.department-targets.index');
            Route::post('hr/department-targets', [DepartmentTargetController::class, 'store'])->name('hr.department-targets.store');

            // Employee Payslip & Salary Balance Portal (All Staff)
            Route::get('payroll/my-payslips', [\App\Http\Controllers\PayrollController::class, 'myPayslips'])->name('payroll.my-payslips');
            Route::get('payroll/payslip/{payslip}/download', [\App\Http\Controllers\PayrollController::class, 'downloadPayslip'])->name('payroll.payslip.download');

            Route::middleware(['permission:hr.view_staff,hr.manage_targets,hr.manage_users,hr.approve_leaves,finance.manage_payroll'])->group(function () {
                Route::get('hr/leaderboard', [HRController::class, 'leaderboard'])->name('hr.leaderboard');
                Route::get('hr/targets', [HRController::class, 'targets'])->name('hr.targets');
                Route::post('hr/targets', [HRController::class, 'storeTarget'])->name('hr.targets.store');
                Route::get('hr/staff/{user}', [StaffProfileController::class, 'show'])->name('hr.staff.show');
                Route::post('hr/staff/{user}/certifications', [StaffProfileController::class, 'storeCertification'])->name('hr.staff.certifications.store');
                Route::delete('hr/staff/certifications/{certification}', [StaffProfileController::class, 'destroyCertification'])->name('hr.staff.certifications.destroy');
                Route::post('hr/staff/{user}/disciplinary', [StaffProfileController::class, 'storeDisciplinary'])->name('hr.staff.disciplinary.store');
                Route::post('hr/staff/{user}/reviews', [StaffProfileController::class, 'storeReview'])->name('hr.staff.reviews.store');
                Route::post('hr/staff/{user}/onboarding', [StaffProfileController::class, 'storeOnboardingTask'])->name('hr.staff.onboarding.store');
                Route::delete('hr/staff/onboarding/{task}', [StaffProfileController::class, 'destroyOnboardingTask'])->name('hr.staff.onboarding.destroy');

                // Payroll & Compensation Management (HR / Finance Desk)
                Route::get('payroll', [\App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
                Route::post('payroll', [\App\Http\Controllers\PayrollController::class, 'store'])->name('payroll.store');
                Route::get('payroll/salaries', [\App\Http\Controllers\PayrollController::class, 'salaryStructures'])->name('payroll.salaries');
                Route::post('payroll/salaries/{user}', [\App\Http\Controllers\PayrollController::class, 'updateSalaryStructure'])->name('payroll.salaries.update');
                Route::post('payroll/deductions', [\App\Http\Controllers\PayrollController::class, 'storeDeduction'])->name('payroll.deductions.store');
                Route::delete('payroll/deductions/{deduction}', [\App\Http\Controllers\PayrollController::class, 'destroyDeduction'])->name('payroll.deductions.destroy');
                Route::get('payroll/{batch}', [\App\Http\Controllers\PayrollController::class, 'show'])->name('payroll.show')->whereNumber('batch');
                Route::post('payroll/{batch}/approve', [\App\Http\Controllers\PayrollController::class, 'approve'])->name('payroll.approve')->whereNumber('batch');
                Route::post('payroll/{batch}/mark-paid', [\App\Http\Controllers\PayrollController::class, 'markPaid'])->name('payroll.mark-paid')->whereNumber('batch');
                Route::get('payroll/{batch}/export-bank', [\App\Http\Controllers\PayrollController::class, 'exportBankCsv'])->name('payroll.export-bank')->whereNumber('batch');
            });

            Route::patch('hr/staff/onboarding/{task}/toggle', [StaffProfileController::class, 'toggleOnboardingTask'])->name('hr.staff.onboarding.toggle');
        });

        // Accounting & Operating Expenses (Finance Desk)
        Route::middleware(['permission:finance.view_ledger,finance.log_expenses,finance.approve_expenses'])->group(function () {
            Route::get('accounting/expenses', [\App\Http\Controllers\ExpenseController::class, 'index'])->name('accounting.expenses.index');
            Route::post('accounting/expenses', [\App\Http\Controllers\ExpenseController::class, 'store'])->name('accounting.expenses.store');
            Route::patch('accounting/expenses/{expense}/status', [\App\Http\Controllers\ExpenseController::class, 'updateStatus'])->name('accounting.expenses.status');
            Route::delete('accounting/expenses/{expense}', [\App\Http\Controllers\ExpenseController::class, 'destroy'])->name('accounting.expenses.destroy');
        });

        // Dynamic Roles & Granular Permissions Engine (Super Admin / Authorized HR)
        Route::middleware(['permission:system.manage_roles'])->group(function () {
            Route::get('settings/roles-permissions', [\App\Http\Controllers\RolePermissionController::class, 'index'])->name('settings.roles.index');
            Route::post('settings/roles-permissions', [\App\Http\Controllers\RolePermissionController::class, 'store'])->name('settings.roles.store');
            Route::get('settings/roles-permissions/{role}/edit', [\App\Http\Controllers\RolePermissionController::class, 'edit'])->name('settings.roles.edit');
            Route::put('settings/roles-permissions/{role}', [\App\Http\Controllers\RolePermissionController::class, 'update'])->name('settings.roles.update');
            Route::delete('settings/roles-permissions/{role}', [\App\Http\Controllers\RolePermissionController::class, 'destroy'])->name('settings.roles.destroy');
        });

        // Construction Inventory Setup & Master Data
        Route::prefix('inventory')->name('inventory.')->group(function () {
            // Visual Step-by-Step Layman Guide (Printable to PDF)
            Route::get('guide', function () {
                return view('inventory.guide.index');
            })->name('guide');

            // Executive Cockpit & Live Cost Valuation Dashboard
            Route::middleware(['permission:inventory.view_reports,inventory.view_stock,finance.view_ledger'])->group(function () {
                Route::get('dashboard', [\App\Http\Controllers\Inventory\InventoryDashboardController::class, 'index'])->name('dashboard');
                Route::get('general-ledger', [\App\Http\Controllers\Inventory\InventoryDashboardController::class, 'generalLedger'])->name('general-ledger.index');
            });

            // Sites & Warehouses
            Route::middleware(['permission:inventory.view_stock,inventory.manage_catalogue'])->group(function () {
                Route::resource('sites', \App\Http\Controllers\Inventory\SiteController::class);
            });

            // Material Catalogue Master & Categories
            Route::middleware(['permission:inventory.manage_catalogue,inventory.view_stock'])->group(function () {
                Route::get('catalogue/api/search', [\App\Http\Controllers\Inventory\MaterialCatalogueController::class, 'apiSearch'])->name('catalogue.api.search');
                Route::resource('catalogue', \App\Http\Controllers\Inventory\MaterialCatalogueController::class);
                Route::resource('categories', \App\Http\Controllers\Inventory\MaterialCategoryController::class)->names('categories');
            });

            // Bill of Materials (BOM) QS Engine
            Route::middleware(['permission:inventory.set_bom,inventory.view_stock,inventory.raise_mrf'])->group(function () {
                Route::get('bom/suggest-qty', [\App\Http\Controllers\Inventory\BOMController::class, 'suggestQty'])->name('bom.suggest-qty');
                Route::resource('bom', \App\Http\Controllers\Inventory\BOMController::class);
            });

            // Suppliers & Vendor Directory
            Route::middleware(['permission:inventory.manage_suppliers,inventory.create_po'])->group(function () {
                Route::resource('suppliers', \App\Http\Controllers\Inventory\SupplierController::class);
                Route::post('suppliers/{supplier}/blacklist', [\App\Http\Controllers\Inventory\SupplierController::class, 'toggleBlacklist'])->name('suppliers.blacklist');
            });

            // Regional Market Price Benchmarks
            Route::middleware(['permission:inventory.manage_benchmarks,inventory.set_bom'])->group(function () {
                Route::get('benchmarks', [\App\Http\Controllers\Inventory\PriceBenchmarkController::class, 'index'])->name('benchmarks.index');
                Route::post('benchmarks', [\App\Http\Controllers\Inventory\PriceBenchmarkController::class, 'store'])->name('benchmarks.store');
                Route::delete('benchmarks/{benchmark}', [\App\Http\Controllers\Inventory\PriceBenchmarkController::class, 'destroy'])->name('benchmarks.destroy');
            });

            // Material Requisitions (MRF)
            Route::middleware(['permission:inventory.raise_mrf,inventory.view_stock'])->group(function () {
                Route::post('requisitions/{requisition}/approve', [\App\Http\Controllers\Inventory\RequisitionController::class, 'approve'])->name('requisitions.approve');
                Route::post('requisitions/{requisition}/reject', [\App\Http\Controllers\Inventory\RequisitionController::class, 'reject'])->name('requisitions.reject');
                Route::resource('requisitions', \App\Http\Controllers\Inventory\RequisitionController::class);
            });

            // Purchase Orders (PO) & Tiered Authorization
            Route::middleware(['permission:inventory.create_po,inventory.approve_po_tier1,inventory.approve_po_tier2,inventory.approve_po_tier3,inventory.view_stock'])->group(function () {
                Route::post('purchase-orders/{purchase_order}/approve', [\App\Http\Controllers\Inventory\PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
                Route::post('purchase-orders/{purchase_order}/reject', [\App\Http\Controllers\Inventory\PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
                Route::resource('purchase-orders', \App\Http\Controllers\Inventory\PurchaseOrderController::class);
            });

            // Goods Received Notes (GRN) & Gate Deliveries
            Route::middleware(['permission:inventory.receive_goods,inventory.view_stock'])->group(function () {
                Route::resource('grn', \App\Http\Controllers\Inventory\GRNController::class);
            });

            // Material Issue Vouchers (MIV) & Site Disbursements
            Route::middleware(['permission:inventory.issue_materials,inventory.view_stock'])->group(function () {
                Route::resource('miv', \App\Http\Controllers\Inventory\MIVController::class);
            });

            // Waste & Material Loss Registry
            Route::middleware(['permission:inventory.log_waste,inventory.view_stock'])->group(function () {
                Route::resource('waste', \App\Http\Controllers\Inventory\WasteLogController::class);
            });

            // 3-Way Match Supplier Invoices
            Route::middleware(['permission:inventory.match_invoices,inventory.view_reports,finance.view_ledger'])->group(function () {
                Route::post('invoices/{invoice}/match', [\App\Http\Controllers\Inventory\SupplierInvoiceController::class, 'runMatch'])->name('invoices.match');
                Route::post('invoices/{invoice}/approve-payment', [\App\Http\Controllers\Inventory\SupplierInvoiceController::class, 'approvePayment'])->name('invoices.approve-payment');
                Route::resource('invoices', \App\Http\Controllers\Inventory\SupplierInvoiceController::class);
            });

            // Fraud Radar & Anomaly Resolution
            Route::middleware(['permission:inventory.view_anomalies,inventory.resolve_anomalies,system.manage_settings'])->group(function () {
                Route::post('anomalies/{anomaly}/status', [\App\Http\Controllers\Inventory\AnomalyController::class, 'updateStatus'])->name('anomalies.update-status');
                Route::resource('anomalies', \App\Http\Controllers\Inventory\AnomalyController::class);
            });

            // Inventory Thresholds & Geofence Settings (Company Admin / Super Admin)
            Route::middleware(['permission:system.manage_settings'])->group(function () {
                Route::get('settings', [\App\Http\Controllers\Inventory\CompanyInventorySettingController::class, 'edit'])->name('settings.edit');
                Route::put('settings', [\App\Http\Controllers\Inventory\CompanyInventorySettingController::class, 'update'])->name('settings.update');
            });
        });

        // Enterprise Real Estate & Construction Accounting Suite
        Route::prefix('accounting')->name('accounting.')->middleware(['permission:finance.view_ledger,inventory.view_reports,finance.view_payouts'])->group(function () {
            // Financial Intelligence Cockpit
            Route::get('dashboard', [\App\Http\Controllers\Accounting\FinancialStatementController::class, 'index'])->name('dashboard');

            // Financial Statements
            Route::prefix('statements')->name('statements.')->group(function () {
                Route::get('balance-sheet', [\App\Http\Controllers\Accounting\FinancialStatementController::class, 'balanceSheet'])->name('balance-sheet');
                Route::get('profit-and-loss', [\App\Http\Controllers\Accounting\FinancialStatementController::class, 'profitAndLoss'])->name('p-and-l');
                Route::get('cash-flow', [\App\Http\Controllers\Accounting\FinancialStatementController::class, 'cashFlow'])->name('cash-flow');
                Route::get('trial-balance', [\App\Http\Controllers\Accounting\FinancialStatementController::class, 'trialBalance'])->name('trial-balance');
            });

            // Multi-Bank Treasury & Automated Bank Reconciliation
            Route::prefix('treasury')->name('treasury.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Accounting\BankTreasuryController::class, 'index'])->name('index');
                Route::post('accounts', [\App\Http\Controllers\Accounting\BankTreasuryController::class, 'storeAccount'])->name('store-account');
                Route::post('import-statement', [\App\Http\Controllers\Accounting\BankTreasuryController::class, 'importStatement'])->name('import-statement');
                Route::post('transactions/{transaction}/match', [\App\Http\Controllers\Accounting\BankTreasuryController::class, 'manualMatch'])->name('manual-match');
            });

            // Debtor & Creditor Aging Matrix (30 / 60 / 90+ Days)
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('ar-aging', [\App\Http\Controllers\Accounting\AgingReportController::class, 'arAging'])->name('ar-aging');
                Route::get('ap-aging', [\App\Http\Controllers\Accounting\AgingReportController::class, 'apAging'])->name('ap-aging');
            });

            // Tax & Statutory Compliance Hub (5% WHT & 7.5% VAT)
            Route::prefix('tax')->name('tax.')->group(function () {
                Route::get('/', [\App\Http\Controllers\Accounting\TaxComplianceController::class, 'index'])->name('index');
            });
        });

        // Customer Portal
        Route::middleware(['role:customer', 'feature:customer_portal'])->group(function () {
            Route::get('buyer/dashboard', [BuyerDashboardController::class, 'index'])->name('buyer.dashboard');
            Route::post('buyer/payments/{milestone}/submit-pop', [BuyerDashboardController::class, 'submitProofOfPayment'])->name('buyer.payments.submit-pop');
            Route::get('buyer/payments/{milestone}/receipt', [BuyerDashboardController::class, 'downloadReceipt'])->name('buyer.payments.receipt');
            Route::get('buyer/documents/{document}/download', [BuyerDashboardController::class, 'downloadDocument'])->name('buyer.documents.download');
            Route::get('buyer/generated-documents/{document}/download', [BuyerDashboardController::class, 'downloadGeneratedDocument'])->name('buyer.generated-documents.download');
        });

        // File Storage
        Route::middleware(['feature:file_manager'])->group(function () {
            Route::get('api/file-storage/{folder?}', [FileStorageController::class, 'apiDirectory'])->name('file-storage.api.directory');
            Route::get('file-storage/{folder?}', [FileStorageController::class, 'index'])->name('file-storage.index');
            Route::post('file-storage/folders', [FileStorageController::class, 'createFolder'])->name('file-storage.folders.store');
            Route::post('file-storage/folders/{folder}/rename', [FileStorageController::class, 'renameFolder'])->name('file-storage.folders.rename');
            Route::delete('file-storage/folders/{folder}', [FileStorageController::class, 'destroyFolder'])->name('file-storage.folders.destroy');
            Route::post('file-storage/files', [FileStorageController::class, 'uploadFile'])->name('file-storage.files.store');
            Route::post('file-storage/files/{file}/rename', [FileStorageController::class, 'renameFile'])->name('file-storage.files.rename');
            Route::get('file-storage/files/{file}/download', [FileStorageController::class, 'download'])->name('file-storage.files.download');
            Route::get('file-storage/files/{file}/preview', [FileStorageController::class, 'preview'])->name('file-storage.files.preview');
            Route::delete('file-storage/files/{file}', [FileStorageController::class, 'destroyFile'])->name('file-storage.files.destroy');
        });

        // Global APIs & Notifications
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('api/global-search', [SearchController::class, 'search'])->name('api.global-search');
        Route::get('api/notifications', [NotificationController::class, 'getAlerts'])->name('api.notifications');
    });

    // Supplier Self-Service Partner Portal (Dedicated Guard)
    Route::prefix('supplier')->name('supplier.')->group(function () {
        Route::get('login', [\App\Http\Controllers\Inventory\SupplierPortal\SupplierAuthController::class, 'showLoginForm'])->name('login');
        Route::post('login', [\App\Http\Controllers\Inventory\SupplierPortal\SupplierAuthController::class, 'login'])->name('login.submit');
        Route::match(['get', 'post'], 'logout', [\App\Http\Controllers\Inventory\SupplierPortal\SupplierAuthController::class, 'logout'])->name('logout');

        Route::middleware(['auth:supplier'])->group(function () {
            Route::get('dashboard', [\App\Http\Controllers\Inventory\SupplierPortal\SupplierDashboardController::class, 'index'])->name('dashboard');
            Route::get('purchase-orders', [\App\Http\Controllers\Inventory\SupplierPortal\SupplierDashboardController::class, 'purchaseOrders'])->name('purchase-orders.index');
            Route::get('purchase-orders/{purchase_order}', [\App\Http\Controllers\Inventory\SupplierPortal\SupplierDashboardController::class, 'showPO'])->name('purchase-orders.show');
            Route::get('invoices', [\App\Http\Controllers\Inventory\SupplierPortal\SupplierDashboardController::class, 'invoices'])->name('invoices.index');
            Route::get('invoices/create', [\App\Http\Controllers\Inventory\SupplierPortal\SupplierDashboardController::class, 'createInvoice'])->name('invoices.create');
            Route::post('invoices', [\App\Http\Controllers\Inventory\SupplierPortal\SupplierDashboardController::class, 'storeInvoice'])->name('invoices.store');
        });
    });
});
