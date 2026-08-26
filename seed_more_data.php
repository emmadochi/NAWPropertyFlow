<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Tenant;
use App\Models\User;
use App\Models\Lead;
use App\Models\Department;
use App\Models\Property;
use Illuminate\Support\Facades\Hash;

// Find the buckcrest tenant
$tenant = Tenant::where('id', 'buckcrest')->first();

if (!$tenant) {
    echo "Tenant 'buckcrest' not found.\n";
    exit;
}

// Initialize Tenancy
tenancy()->initialize($tenant);

// Get Sales Department
$salesDept = Department::where('name', 'Sales')->first();
$deptId = $salesDept ? $salesDept->id : null;

// Create 2 Sales Executives
$se1 = User::firstOrCreate(
    ['email' => 'chioma@buckcresthavensltd.org'],
    [
        'name' => 'Chioma Adebayo (Sales Exec)',
        'password' => Hash::make('password'),
        'role' => 'sales_executive',
        'department_id' => $deptId,
        'department' => 'Sales',
        'status' => 'active',
    ]
);

$se2 = User::firstOrCreate(
    ['email' => 'michael@buckcresthavensltd.org'],
    [
        'name' => 'Michael Johnson (Sales Exec)',
        'password' => Hash::make('password'),
        'role' => 'sales_executive',
        'department_id' => $deptId,
        'department' => 'Sales',
        'status' => 'active',
    ]
);

echo "Created Sales Executives.\n";

// Get some properties
$properties = Property::take(3)->get();
if ($properties->isEmpty()) {
    echo "No properties found. Creating a dummy property...\n";
    $prop = Property::create([
        'name' => 'Luxury Villa Asokoro',
        'type' => 'Villa',
        'status' => 'available',
        'price' => 150000000,
    ]);
    $properties->push($prop);
}

// Create 10 leads assigned to the new sales executives
$faker = Faker\Factory::create('en_NG');
$sources = ['Website', 'Referral', 'Instagram', 'Walk-in', 'LinkedIn'];
$statuses = ['new', 'contacted', 'qualified', 'proposal', 'negotiation'];

$execs = [$se1->id, $se2->id];

for ($i = 1; $i <= 20; $i++) {
    Lead::create([
        'full_name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'phone_number' => $faker->phoneNumber,
        'lead_status' => $faker->randomElement($statuses),
        'lead_source' => $faker->randomElement($sources),
        'assigned_to' => $faker->randomElement($execs),
        'property_interest_id' => $properties->random()->id,
        'budget' => (string)($faker->numberBetween(50, 500) * 1000000),
        'notes' => $faker->sentence,
    ]);
}

echo "Created 20 new Leads assigned to them.\n";
