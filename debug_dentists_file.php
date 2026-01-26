<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ds = App\Models\Dentist::with('user')->get();
$out = "";
foreach($ds as $d){
    $out .= "ID: {$d->id} | Name: {$d->name} | Status: {$d->status} | UserID: {$d->user->id} | UserStatus: " . ($d->user->status ?? 'NULL') . "\n";
}
file_put_contents('dentists_list_log.txt', $out);
echo "Done.";
