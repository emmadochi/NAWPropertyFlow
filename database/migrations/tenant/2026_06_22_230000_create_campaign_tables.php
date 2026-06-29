<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Campaigns ───────────────────────────────────────────
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // email | sms | whatsapp
            $table->string('status')->default('draft'); // draft | scheduled | sending | sent | paused | cancelled
            $table->string('subject')->nullable();      // email subject
            $table->longText('body');                   // HTML body (email) or plain text (sms)
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('audience_segment')->default('all'); // all | status:hot | source:instagram | branch:1 | custom
            $table->json('audience_filters')->nullable(); // e.g. { "status": "hot", "lead_source": "Instagram" }
            $table->unsignedInteger('audience_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('opened_count')->default(0);
            $table->unsignedInteger('clicked_count')->default(0);
            $table->unsignedInteger('unsubscribed_count')->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // ─── Campaign Contacts (who received the campaign) ───────
        Schema::create('campaign_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending | sent | delivered | opened | clicked | failed | unsubscribed
            $table->string('tracking_token')->nullable()->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();
            $table->unique(['campaign_id', 'lead_id']);
        });

        // ─── Drip Sequences ──────────────────────────────────────
        Schema::create('drip_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('trigger_event'); // lead_created | status_changed:hot | inspection_booked | deal_won
            $table->json('trigger_conditions')->nullable(); // extra filter conditions
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        // ─── Drip Steps ──────────────────────────────────────────
        Schema::create('drip_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drip_sequence_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('type'); // email | sms | whatsapp
            $table->string('subject')->nullable();
            $table->longText('body');
            $table->unsignedInteger('delay_days')->default(0); // days after previous step
            $table->unsignedInteger('delay_hours')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ─── Drip Enrollments (leads enrolled in a drip) ─────────
        Schema::create('drip_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drip_sequence_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('current_step_id')->nullable()->constrained('drip_steps')->nullOnDelete();
            $table->string('status')->default('active'); // active | paused | completed | cancelled
            $table->timestamp('next_send_at')->nullable();
            $table->timestamp('enrolled_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['drip_sequence_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drip_enrollments');
        Schema::dropIfExists('drip_steps');
        Schema::dropIfExists('drip_sequences');
        Schema::dropIfExists('campaign_contacts');
        Schema::dropIfExists('campaigns');
    }
};
