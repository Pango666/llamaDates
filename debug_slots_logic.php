<?php

require __DIR__.'/vendor/autoload.php';

use Carbon\Carbon;

echo "--- SIMULATION OF SLOTS LOGIC ---\n";

// 1. Setup Environment
$date = '2026-01-29';
$now  = Carbon::parse('2026-01-29 10:15:00'); // Simulate NOW is 10:15 AM
echo "Simulated NOW: " . $now->format('H:i') . "\n";

// 2. Dentist Schedule
$startWork = Carbon::parse($date . ' 09:00:00');
$endWork   = Carbon::parse($date . ' 13:00:00');
$duration  = 30; // minutes

// 3. Existing Appointments (Active)
$appointments = [
    ['start' => '11:00:00', 'end' => '11:30:00']
];

echo "Schedule: 09:00 - 13:00\n";
echo "Existing Appt: 11:00 - 11:30\n";
echo "Logic: Fixed Grid + Skip Past\n\n";

// 4. Run Logic (Copied from Controller)
$slots = [];
$current = $startWork->copy();
$isToday = true; // Since date matches now

while ($current->copy()->addMinutes($duration)->lte($endWork)) {
    $slotStart = $current->copy();
    $slotEnd   = $current->copy()->addMinutes($duration);

    // LOGIC CHECK: Skip past
    if ($isToday && $slotStart->lt($now)) {
        echo "[{$slotStart->format('H:i')} - {$slotEnd->format('H:i')}] -> SKIPPED (Past)\n";
        $current->addMinutes($duration);
        continue;
    }
    
    $isFree = true;
    foreach ($appointments as $appt) {
        $apptStart = Carbon::parse($date . ' ' . $appt['start']);
        $apptEnd   = Carbon::parse($date . ' ' . $appt['end']);

        // Check overlap
        if ($slotStart->lt($apptEnd) && $slotEnd->gt($apptStart)) {
            $isFree = false;
            echo "[{$slotStart->format('H:i')} - {$slotEnd->format('H:i')}] -> BUSY (Overlap 11:00)\n";
            break;
        }
    }

    if ($isFree) {
        echo "[{$slotStart->format('H:i')} - {$slotEnd->format('H:i')}] -> AVAILABLE\n";
        $slots[] = $slotStart->format('H:i');
    }

    $current->addMinutes($duration);
}

echo "\nResulting Slots for User: " . implode(', ', $slots) . "\n";
