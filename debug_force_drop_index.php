<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- CHECKING INDICES ON 'appointments' ---\n";

$indexes = DB::select("SHOW INDEXES FROM appointments");
$found = false;

foreach ($indexes as $idx) {
    if ($idx->Key_name === 'uniq_slot_active') {
        echo "FOUND INDEX: uniq_slot_active\n";
        $found = true;
        break;
    }
}

if ($found) {
    echo "Attempting to DROP index 'uniq_slot_active'...\n";
    try {
        DB::statement("DROP INDEX uniq_slot_active ON appointments");
        echo "DROP COMMAND EXECUTED SUCCESSFULLY.\n";
    } catch (\Exception $e) {
        echo "ERROR DROPPING INDEX: " . $e->getMessage() . "\n";
    }
} else {
    echo "Index 'uniq_slot_active' NOT FOUND. (Already dropped?)\n";
}

echo "--- VERIFYING ---\n";
$indexesAfter = DB::select("SHOW INDEXES FROM appointments");
$stillExists = false;
foreach ($indexesAfter as $idx) {
    if ($idx->Key_name === 'uniq_slot_active') {
        $stillExists = true;
    }
}

if ($stillExists) {
    echo "CRITICAL: Index STILL EXISTS.\n";
} else {
    echo "SUCCESS: Index is GONE.\n";
}
