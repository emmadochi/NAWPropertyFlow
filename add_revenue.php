<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\Sale;
use App\Models\Lead;

// Find the buckcrest tenant
$tenant = Tenant::where('id', 'buckcrest')->first();

if (!$tenant) {
    echo "Tenant 'buckcrest' not found.\n";
    exit;
}

// Initialize Tenancy
tenancy()->initialize($tenant);

$currentRevenue = Sale::where('status', 'Closed Won')->sum('deal_value');
$targetRevenue = 700000000;
$difference = $targetRevenue - $currentRevenue;

if ($difference > 0) {
    // Find or create a Lead to attach the sale to
    $lead = Lead::first();
    
    Sale::create([
        'lead_id' => $lead->id,
        'property_id' => $lead->property_interest_id ?? 1,
        'sales_officer_id' => $lead->assigned_to,
        'deal_value' => $difference,
        'status' => 'Closed Won',
        'deal_closed_at' => now(),
    ]);
    
    echo "Added a new sale of " . number_format($difference) . " to reach exactly 700 million.\n";
} else {
    echo "Total revenue is already " . number_format($currentRevenue) . " which is >= 700 million.\n";
}
