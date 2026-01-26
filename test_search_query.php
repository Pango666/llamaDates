<?php

use App\Models\Appointment;
use App\Models\Dentist;
use Illuminate\Http\Request;

// Mock request
$r = new Request([
    'q' => 'juan', // Testing search
    // 'dentist_id' => 1
]);

echo "Testing Appointment Search Logic...\n";

try {
    $base = Appointment::query()
            ->with([
                'patient:id,first_name,last_name,phone',
                'service:id,name',
                'dentist:id,name',
            ]);

    if ($r->filled('q')) {
        $q = trim(mb_strtolower($r->q));
        echo "Search Term: '$q'\n";
        
        $base->where(function ($qq) use ($q) {
            $qq->whereHas('patient', function ($p) use ($q) {
                $p->whereRaw('LOWER(first_name) LIKE ?', ["%{$q}%"])
                  ->orWhereRaw('LOWER(last_name) LIKE ?',  ["%{$q}%"])
                  ->orWhereRaw('LOWER(phone) LIKE ?',      ["%{$q}%"]);
            })->orWhereHas('service', function ($s) use ($q) {
                $s->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"]);
            })->orWhereHas('dentist', function ($d) use ($q) {
                $d->whereRaw('LOWER(name) LIKE ?', ["%{$q}%"]);
            });
        });
    }

    echo "Query SQL: " . $base->toSql() . "\n";
    $count = $base->count();
    echo "Found: $count appointments.\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
