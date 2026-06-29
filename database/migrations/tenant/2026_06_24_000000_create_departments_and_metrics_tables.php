<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->default('🏢');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Create department_metrics table
        Schema::create('department_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->string('key');
            $table->string('label');
            $table->string('unit')->default('count'); // count, currency, percent
            $table->string('type')->default('manual'); // system, manual
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['department_id', 'key']);
        });

        // 3. Add department_id and actual_value to department_targets
        Schema::table('department_targets', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('cascade');
            $table->decimal('actual_value', 15, 2)->nullable();
        });

        // 4. Add department_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
        });

        // 5. Seed Default Departments and Metrics
        $depts = [
            'Sales' => [
                'description' => 'Sales and business development unit.',
                'icon' => '💰',
                'metrics' => [
                    ['key' => 'revenue', 'label' => 'Total Revenue Generated', 'unit' => 'currency', 'type' => 'system'],
                    ['key' => 'deals_closed', 'label' => 'Closed Sales Deals', 'unit' => 'count', 'type' => 'system'],
                    ['key' => 'leads_contacted', 'label' => 'Follow-Ups Completed', 'unit' => 'count', 'type' => 'system']
                ]
            ],
            'Media' => [
                'description' => 'Marketing, social media, and campaign creators.',
                'icon' => '📱',
                'metrics' => [
                    ['key' => 'campaigns_sent', 'label' => 'Marketing Campaigns Sent', 'unit' => 'count', 'type' => 'system'],
                    ['key' => 'leads_generated', 'label' => 'New Leads Acquired', 'unit' => 'count', 'type' => 'system'],
                    ['key' => 'videos_shot', 'label' => 'Videos Shot', 'unit' => 'count', 'type' => 'manual'],
                    ['key' => 'graphics_done', 'label' => 'Graphics Designed', 'unit' => 'count', 'type' => 'manual']
                ]
            ],
            'Project Management' => [
                'description' => 'Site engineers and construction milestone managers.',
                'icon' => '🏗️',
                'metrics' => [
                    ['key' => 'milestones_completed', 'label' => 'Construction Milestones Completed', 'unit' => 'count', 'type' => 'system'],
                    ['key' => 'inspections_conducted', 'label' => 'Completed Site Inspections', 'unit' => 'count', 'type' => 'system']
                ]
            ],
            'Admin' => [
                'description' => 'Human resources, operations, and general admin.',
                'icon' => '⚙️',
                'metrics' => [
                    ['key' => 'leaves_processed', 'label' => 'Leave Requests Audited', 'unit' => 'count', 'type' => 'system'],
                    ['key' => 'users_created', 'label' => 'Staff Inductions Executed', 'unit' => 'count', 'type' => 'system']
                ]
            ]
        ];

        foreach ($depts as $name => $info) {
            $deptId = DB::table('departments')->insertGetId([
                'name' => $name,
                'description' => $info['description'],
                'icon' => $info['icon'],
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach ($info['metrics'] as $metric) {
                DB::table('department_metrics')->insert([
                    'department_id' => $deptId,
                    'key' => $metric['key'],
                    'label' => $metric['label'],
                    'unit' => $metric['unit'],
                    'type' => $metric['type'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Map existing users with this string department to the department_id
            DB::table('users')
                ->where('department', $name)
                ->update(['department_id' => $deptId]);

            // Map existing targets with this string department to the department_id
            DB::table('department_targets')
                ->where('department', $name)
                ->update(['department_id' => $deptId]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });

        Schema::table('department_targets', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['department_id', 'actual_value']);
        });

        Schema::dropIfExists('department_metrics');
        Schema::dropIfExists('departments');
    }
};
