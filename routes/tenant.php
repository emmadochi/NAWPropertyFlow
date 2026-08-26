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

Route::middleware([
    'web',
])->group(function () {

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
        Route::resource('leads', LeadController::class);
        Route::post('leads/{lead}/assign', [LeadController::class, 'assign'])->name('leads.assign');
        Route::patch('leads/{lead}/status', [LeadController::class, 'updateStatus'])->name('leads.update-status');
        Route::post('leads/{lead}/notes', [LeadController::class, 'storeNote'])->name('leads.notes.store');

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
        });

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
            Route::resource('campaigns', CampaignController::class)->except(['edit', 'update']);
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
            // Executive Cockpit & Live Cost Valuation Dashboard
            Route::middleware(['permission:inventory.view_reports,inventory.view_stock,finance.view_ledger'])->group(function () {
                Route::get('dashboard', [\App\Http\Controllers\Inventory\InventoryDashboardController::class, 'index'])->name('dashboard');
                Route::get('general-ledger', [\App\Http\Controllers\Inventory\InventoryDashboardController::class, 'generalLedger'])->name('general-ledger.index');
            });

            // Sites & Warehouses
            Route::middleware(['permission:inventory.view_stock,inventory.manage_catalogue'])->group(function () {
                Route::resource('sites', \App\Http\Controllers\Inventory\SiteController::class);
            });

            // Material Catalogue Master
            Route::middleware(['permission:inventory.manage_catalogue,inventory.view_stock'])->group(function () {
                Route::get('catalogue/api/search', [\App\Http\Controllers\Inventory\MaterialCatalogueController::class, 'apiSearch'])->name('catalogue.api.search');
                Route::resource('catalogue', \App\Http\Controllers\Inventory\MaterialCatalogueController::class);
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
