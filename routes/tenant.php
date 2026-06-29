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
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    // Landing redirect
    Route::get('/', function () {
        return redirect()->route('login');
    });

    // Campaign Tracking (public, within tenant context)
    Route::get('campaigns/track/open/{token}', [CampaignController::class, 'trackOpen'])->name('campaigns.track.open');
    Route::get('campaigns/track/click/{token}', [CampaignController::class, 'trackClick'])->name('campaigns.track.click');

    // Guest Routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login']);
        Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'handleForgotPassword'])->name('password.email');
    });

    // Authenticated Routes
    Route::middleware(['auth'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

            Route::get('generated-documents', [GeneratedDocumentController::class, 'index'])->name('generated-documents.index');
            Route::get('generated-documents/{document}', [GeneratedDocumentController::class, 'show'])->name('generated-documents.show');
            Route::get('generated-documents/{document}/download', [GeneratedDocumentController::class, 'download'])->name('generated-documents.download');
            Route::post('generated-documents/{document}/email', [GeneratedDocumentController::class, 'email'])->name('generated-documents.email');

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
        Route::middleware(['role:super_admin,company_admin,hr,sales_manager', 'feature:advanced_reports'])->group(function () {
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
        Route::middleware(['role:super_admin,company_admin,sales_manager', 'feature:marketing'])->group(function () {
            Route::resource('campaigns', CampaignController::class)->except(['edit', 'update']);
            Route::post('campaigns/{campaign}/send', [CampaignController::class, 'send'])->name('campaigns.send');
            Route::post('campaigns/preview-audience', [CampaignController::class, 'previewAudience'])->name('campaigns.preview-audience');
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

            Route::middleware(['role:super_admin,company_admin,hr,sales_manager'])->group(function () {
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
            });

            Route::patch('hr/staff/onboarding/{task}/toggle', [StaffProfileController::class, 'toggleOnboardingTask'])->name('hr.staff.onboarding.toggle');
        });

        // Customer Portal
        Route::middleware(['role:customer', 'feature:customer_portal'])->group(function () {
            Route::get('buyer/dashboard', [BuyerDashboardController::class, 'index'])->name('buyer.dashboard');
            Route::get('buyer/payments/{milestone}/receipt', [BuyerDashboardController::class, 'downloadReceipt'])->name('buyer.payments.receipt');
            Route::get('buyer/documents/{document}/download', [BuyerDashboardController::class, 'downloadDocument'])->name('buyer.documents.download');
            Route::get('buyer/generated-documents/{document}/download', [BuyerDashboardController::class, 'downloadGeneratedDocument'])->name('buyer.generated-documents.download');
        });

        // File Storage
        Route::middleware(['feature:file_manager'])->group(function () {
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

        // Global APIs
        Route::get('api/global-search', [SearchController::class, 'search'])->name('api.global-search');
        Route::get('api/notifications', [NotificationController::class, 'getAlerts'])->name('api.notifications');
    });
});
