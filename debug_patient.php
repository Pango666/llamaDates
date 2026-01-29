<?php

use App\Models\Patient;
use App\Models\User;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$patients = Patient::latest()->take(5)->get();
echo "Listing last 5 patients:\n";
foreach($patients as $p) {
    echo "ID: {$p->id} | Name: {$p->first_name} {$p->last_name} | CI: {$p->ci} | Email: {$p->email} | Active: {$p->is_active}\n";
}

$u = User::latest()->take(5)->get();
echo "Listing last 5 users:\n";
foreach($u as $user) {
    echo "ID: {$user->id} | Name: {$user->name} | Email: {$user->email} | Status: {$user->status}\n";
}

