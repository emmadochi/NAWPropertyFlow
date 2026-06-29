<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. document_templates
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('trigger_event'); // deal_won, payment_received, inspection_completed
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. document_template_versions
        Schema::create('document_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_id')->constrained()->cascadeOnDelete();
            $table->integer('version_number')->default(1);
            $table->longText('content'); // HTML content with tokens
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['document_template_id', 'version_number'], 'doc_template_version_unique');
        });

        // 3. generated_documents
        Schema::create('generated_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('content'); // parsed HTML content
            $table->string('pdf_path')->nullable();
            $table->foreignId('generated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('generated_documents');
        Schema::dropIfExists('document_template_versions');
        Schema::dropIfExists('document_templates');
    }
};
