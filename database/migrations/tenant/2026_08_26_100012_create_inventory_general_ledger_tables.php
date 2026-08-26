<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_code', 20)->unique();
            $table->string('account_name', 100);
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed Core Construction Inventory & Real Estate Accounts
        DB::table('inventory_chart_of_accounts')->insert([
            [
                'account_code' => '1300',
                'account_name' => 'Construction Materials Inventory Asset',
                'account_type' => 'asset',
                'description' => 'Unissued physical raw materials on site yards (cement, rebar, granite, blocks).',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_code' => '1400',
                'account_name' => 'Input VAT Recoverable (7.5%)',
                'account_type' => 'asset',
                'description' => 'VAT paid on incoming vendor material purchases.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_code' => '2100',
                'account_name' => 'Accounts Payable — Trade Suppliers',
                'account_type' => 'liability',
                'description' => 'Approved net vendor payment liabilities awaiting EFT disbursement.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_code' => '2120',
                'account_name' => 'Withholding Tax (WHT 5%) Payable',
                'account_type' => 'liability',
                'description' => 'Statutory 5% tax deducted at source for remittance to FIRS / State Internal Revenue.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_code' => '2150',
                'account_name' => 'Goods Received Not Invoiced (GRNI Accrual)',
                'account_type' => 'liability',
                'description' => 'Accrual clearing account for received gate deliveries awaiting 3-way match invoice.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_code' => '5100',
                'account_name' => 'Direct Construction Materials & Job Costing (WIP)',
                'account_type' => 'expense',
                'description' => 'Cost of materials issued and incorporated into active building projects.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account_code' => '5190',
                'account_name' => 'Material Shrinkage, Scrap & Waste Write-Off',
                'account_type' => 'expense',
                'description' => 'Unavoidable/avoidable site material waste and damage loss expensed to P&L.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        Schema::create('inventory_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 50)->unique();
            $table->date('entry_date');
            $table->string('reference_type')->nullable(); // GoodsReceivedNote, SupplierInvoice, MaterialIssueVoucher, WasteLog
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('site_id')->nullable()->constrained('inventory_sites')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('description');
            $table->decimal('total_debit', 15, 2)->default(0);
            $table->decimal('total_credit', 15, 2)->default(0);
            $table->boolean('is_balanced')->default(true);
            $table->unsignedBigInteger('posted_by_user_id')->nullable();
            $table->foreign('posted_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });

        Schema::create('inventory_journal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained('inventory_journal_entries')->cascadeOnDelete();
            $table->string('account_code', 20);
            $table->foreign('account_code')->references('account_code')->on('inventory_chart_of_accounts')->cascadeOnDelete();
            $table->enum('entry_type', ['debit', 'credit']);
            $table->decimal('amount', 15, 2);
            $table->string('narration')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_journal_items');
        Schema::dropIfExists('inventory_journal_entries');
        Schema::dropIfExists('inventory_chart_of_accounts');
    }
};
