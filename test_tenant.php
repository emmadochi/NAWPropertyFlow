<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\User;

$tenant = Tenant::find('naw');
if ($tenant) {
    $tenant->run(function () {
        $user = User::where('email', 'naw@nawworld.com')->first();
        if ($user) {
            $user->password = Hash::make('Password123!');
            $user->save();
            echo "Password successfully reset for naw@nawworld.com" . PHP_EOL;
        } else {
            echo "User naw@nawworld.com not found in tenant naw" . PHP_EOL;
        }
    });
} else {
    echo "Tenant 'naw' not found" . PHP_EOL;
}
