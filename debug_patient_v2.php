<?php
use App\Models\Patient;
use App\Models\User;
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$out = "--- DEBUG REPORT ---\n";
// Specific patient from screenshot
$out .= "Searching for CI [12478159]...\n";
$p = Patient::where('ci', '12478159')->first();

if (!$p) {
    $out .= "Patient NOT found by CI. Trying Last Name 'Galvez'...\n";
    $p = Patient::where('last_name', 'like', '%Galvez%')->latest()->first();
}

if ($p) {
    $out .= "PATIENT FOUND: ID [{$p->id}]\n";
    $out .= "Name: {$p->first_name} {$p->last_name}\n";
    $out .= "Email: {$p->email}\n";
    $out .= "Is_Active: " . ($p->is_active ? '1 (Active)' : '0 (Inactive)') . "\n";
    $out .= "User_ID (FK): " . ($p->user_id ?? 'NULL') . "\n";

    if ($p->user_id) {
        $u = User::find($p->user_id);
        if ($u) {
            $out .= "LINKED USER: ID [{$u->id}], Status [{$u->status}], Email [{$u->email}]\n";
        } else {
            $out .= "LINKED USER: ID [{$p->user_id}] NOT FOUND IN DB.\n";
        }
    } else {
        $out .= "LINKED USER: NONE (NULL FK).\n";
    }

    // Check Orphan by Email
    if ($p->email) {
        $u2 = User::where('email', $p->email)->first();
        if ($u2) {
            $out .= "USER BY EMAIL: ID [{$u2->id}], Status [{$u2->status}], Email [{$u2->email}]\n";
            if ($p->user_id != $u2->id) {
                $out .= "!!! MISMATCH !!! Patient points to user_id [{$p->user_id}] but User with same email has ID [{$u2->id}].\n";
            } else {
                $out .= "OK: Email match confirms link.\n";
            }
        } else {
            $out .= "USER BY EMAIL: Not found.\n";
        }
    }
} else {
    $out .= "Patient 'Galvez' or CI '12478159' NOT FOUND.\n";
}

$out .= "\n--- LATEST 3 PATIENTS ---\n";
foreach(Patient::latest()->take(3)->get() as $lp) {
    $out .= "ID: {$lp->id}, Name: {$lp->first_name}, Active: {$lp->is_active}, User_ID: {$lp->user_id}\n";
}

file_put_contents('debug_final.txt', $out);
