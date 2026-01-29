<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "--- NUCLEAR INDEX REMOVAL ---\n";

try {
    // 1. Drop Foreign Keys that might rely on the index
    // Standard Laravel naming: appointments_dentist_id_foreign
    echo "1. Dropping FK 'appointments_dentist_id_foreign'...\n";
    Schema::table('appointments', function (Blueprint $table) {
        $table->dropForeign(['dentist_id']); 
    });
    echo "   FK Dropped (or handled gracefully by Schema builder).\n";

} catch (\Exception $e) {
    echo "   Warning dropping FK: " . $e->getMessage() . "\n";
}

try {
    // 2. Drop the Index
    echo "2. Dropping Index 'uniq_slot_active'...\n";
    DB::statement("DROP INDEX uniq_slot_active ON appointments");
    echo "   SUCCESS: Index Dropped.\n";
} catch (\Exception $e) {
    echo "   Error dropping index: " . $e->getMessage() . "\n";
}

try {
    // 3. Re-Add Foreign Key
    echo "3. Restoring FK 'appointments_dentist_id_foreign'...\n";
    Schema::table('appointments', function (Blueprint $table) {
        $table->foreign('dentist_id')
              ->references('id')->on('dentists')
              ->cascadeOnDelete();
    });
    echo "   FK Restored.\n";
} catch (\Exception $e) {
     echo "   Error restoring FK: " . $e->getMessage() . "\n";
}

echo "--- FINAL STATUS ---\n";
$indexes = DB::select("SHOW INDEXES FROM appointments");
$exists = false;
foreach ($indexes as $idx) {
    if ($idx->Key_name === 'uniq_slot_active') $exists = true;
}
if ($exists) echo "FAILURE: Index still exists.\n";
else echo "VICTORY: Index is gone.\n";
