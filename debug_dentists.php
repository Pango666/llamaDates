<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ds = App\Models\Dentist::with('user')->get();
foreach($ds as $d){
    echo "ID: {$d->id} | Name: {$d->name} | Status: {$d->status} | UserID: {$d->user_id} | UserStatus: " . ($d->user->status ?? 'NULL') . "\n";
}
