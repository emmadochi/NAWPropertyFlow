<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Expand Chart of Accounts with standard IFRS Real Estate & Construction accounts
        $additionalAccounts = [
            ['account_code' => '1010', 'account_name' => 'Cash at Bank — Operating Account', 'account_type' => 'asset', 'description' => 'Main corporate operating current account for contractor disbursements and overheads.'],
            ['account_code' => '1015', 'account_name' => 'Cash at Bank — Collections Account', 'account_type' => 'asset', 'description' => 'Dedicated buyer escrow/inflow bank account for property purchase milestones.'],
            ['account_code' => '1020', 'account_name' => 'Site Petty Cash & Incidentals Float', 'account_type' => 'asset', 'description' => 'On-site imprest for emergency materials, logistics, and minor site repairs.'],
            ['account_code' => '1200', 'account_name' => 'Accounts Receivable — Property Installments', 'account_type' => 'asset', 'description' => 'Committed buyer installment balances due on off-plan and completed sales.'],
            ['account_code' => '1500', 'account_name' => 'Land & Off-Plan Development WIP Asset', 'account_type' => 'asset', 'description' => 'Capitalized land banking costs and direct infrastructure development in progress.'],
            ['account_code' => '1600', 'account_name' => 'Construction Plant, Machinery & Equipment', 'account_type' => 'asset', 'description' => 'Concrete mixers, excavators, formwork, and heavy site machinery.'],
            ['account_code' => '2130', 'account_name' => 'Output VAT (7.5%) Payable', 'account_type' => 'liability', 'description' => 'Value Added Tax collected on commercial property sales and facility fees.'],
            ['account_code' => '2200', 'account_name' => 'Accrued Salaries, Wages & Pension Liabilities', 'account_type' => 'liability', 'description' => 'Outstanding monthly employee payroll and statutory pension withholdings.'],
            ['account_code' => '2300', 'account_name' => 'Customer Advance Deposits & Escrow', 'account_type' => 'liability', 'description' => 'Buyer initial deposits awaiting formal contract execution and unit allocation.'],
            ['account_code' => '3010', 'account_name' => 'Share Capital & Paid-in Equity', 'account_type' => 'equity', 'description' => 'Owners and shareholders invested equity capital.'],
            ['account_code' => '3100', 'account_name' => 'Retained Earnings', 'account_type' => 'equity', 'description' => 'Cumulative undistributed prior year profits retained in the business.'],
            ['account_code' => '4100', 'account_name' => 'Revenue — Residential Property Sales', 'account_type' => 'revenue', 'description' => 'Gross proceeds realized from terrace, duplex, and apartment unit sales.'],
            ['account_code' => '4200', 'account_name' => 'Revenue — Commercial Property & Plaza Sales', 'account_type' => 'revenue', 'description' => 'Sales of office towers, commercial shops, and warehouse bays.'],
            ['account_code' => '4300', 'account_name' => 'Revenue — Real Estate Agency & Advisory', 'account_type' => 'revenue', 'description' => 'Brokerage commissions, title documentation, and property management fees.'],
            ['account_code' => '4400', 'account_name' => 'Revenue — Estate Service Charges & FM', 'account_type' => 'revenue', 'description' => 'Resident facility management, security levy, and utility service subscriptions.'],
            ['account_code' => '5200', 'account_name' => 'Direct Site Labor & Subcontractor Costs', 'account_type' => 'expense', 'description' => 'Masons, iron-benders, carpenters, plumbers, and specialty subcontractors.'],
            ['account_code' => '5300', 'account_name' => 'Heavy Equipment Hire & Fuel Costs', 'account_type' => 'expense', 'description' => 'Excavator, crane, and generator diesel expenditure on construction sites.'],
            ['account_code' => '6100', 'account_name' => 'Staff Salaries, Wages & Staff Welfare', 'account_type' => 'expense', 'description' => 'Monthly permanent staff payroll, HMO, pensions, and staff allowances.'],
            ['account_code' => '6200', 'account_name' => 'Sales Commissions & Broker Incentives', 'account_type' => 'expense', 'description' => 'Direct agent success fees paid on closed property transactions.'],
            ['account_code' => '6300', 'account_name' => 'Marketing, Billboards & Digital Ads', 'account_type' => 'expense', 'description' => 'Google ads, Meta marketing, billboards, site signage, and expo branding.'],
            ['account_code' => '6400', 'account_name' => 'Office Rent, Utilities & Administrative', 'account_type' => 'expense', 'description' => 'Head office rent, internet, stationeries, local transport, and power.'],
            ['account_code' => '6500', 'account_name' => 'Legal, Audit & Professional Consulting', 'account_type' => 'expense', 'description' => 'Conveyancing attorneys, statutory audit, and tax consulting services.'],
        ];

        foreach ($additionalAccounts as $acc) {
            DB::table('inventory_chart_of_accounts')->updateOrInsert(
                ['account_code' => $acc['account_code']],
                array_merge($acc, ['is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // 2. Corporate Bank Accounts & Treasury
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_name', 100); // e.g. "Zenith Main Operations"
            $table->string('bank_name', 100);    // e.g. "Zenith Bank Plc"
            $table->string('account_number', 30);
            $table->string('currency', 10)->default('NGN');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->string('gl_account_code', 20)->default('1010');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('gl_account_code')->references('account_code')->on('inventory_chart_of_accounts')->cascadeOnDelete();
        });

        // 3. Bank Statement Transactions & Automated Reconciliation Ledger
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->date('transaction_date');
            $table->enum('type', ['credit', 'debit']); // credit = money in, debit = money out
            $table->decimal('amount', 15, 2);
            $table->string('reference', 100)->nullable();
            $table->string('narration')->nullable();
            $table->boolean('reconciled')->default(false);
            $table->timestamp('reconciled_at')->nullable();
            $table->unsignedBigInteger('reconciled_by_user_id')->nullable();
            $table->foreign('reconciled_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained('inventory_journal_entries')->nullOnDelete();
            $table->string('matched_entity_type')->nullable(); // e.g. SupplierInvoice, PaymentMilestone, Expense, Payroll
            $table->unsignedBigInteger('matched_entity_id')->nullable();
            $table->timestamps();

            $table->index(['bank_account_id', 'transaction_date']);
            $table->index(['matched_entity_type', 'matched_entity_id']);
        });

        // 4. Tax Records (5% WHT, 7.5% VAT, Stamp Duty)
        Schema::create('tax_records', function (Blueprint $table) {
            $table->id();
            $table->enum('tax_type', ['wht_5', 'wht_10', 'vat_7_5', 'stamp_duty'])->default('wht_5');
            $table->string('entity_type')->nullable(); // SupplierInvoice, Sales, Expense
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('tax_rate_pct', 5, 2)->default(5.00);
            $table->decimal('tax_amount', 15, 2);
            $table->string('beneficiary_name')->nullable();
            $table->string('beneficiary_tin', 50)->nullable();
            $table->enum('status', ['accrued', 'remitted'])->default('accrued');
            $table->timestamp('remitted_at')->nullable();
            $table->string('remittance_reference', 100)->nullable();
            $table->unsignedBigInteger('recorded_by_user_id')->nullable();
            $table->foreign('recorded_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tax_type', 'status']);
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_records');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
    }
};
