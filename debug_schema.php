<?php

$columns = Illuminate\Support\Facades\Schema::getColumnListing('appointments');
$details = [];

foreach ($columns as $col) {
    // Get column type/details if possible, or just name
    $type = Illuminate\Support\Facades\Schema::getColumnType('appointments', $col);
    // Not Null check is harder without Doctrine dbal fully configured sometimes, so we verify with simple listing
    $details[] = "$col ($type)";
}

file_put_contents('debug_schema.txt', implode("\n", $details));
echo "Schema saved.\n";

// Also try to catch the exact create error
try {
    // Attempt a dummy create to get the exception message
    App\Models\Appointment::create([
        'patient_id' => 67, 
        'dentist_id' => 1, 
        'chair_id' => 1,
        'service_id' => 1, 
        'date' => '2026-02-02', 
        'start_time' => '09:00', 
        'end_time' => '09:30', 
        'status' => 'reserved', 
        'notes' => 'Test'
    ]);
} catch (\Exception $e) {
    file_put_contents('debug_error.txt', $e->getMessage());
    echo "Error saved.\n";
}
