<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Tenant::create([
    'id' => 'buckcrest', 
    'company_name' => 'Buckcrest Havens Ltd',
    'admin_name' => 'Admin User',
    'admin_email' => 'admin@buckcresthavensltd.org',
    'admin_password' => bcrypt('password'),
    'plan' => 'premium',
    'status' => 'active'
]);
$tenant->domains()->create(['domain' => 'buckcrest.localhost']);
echo "Tenant created successfully.";
