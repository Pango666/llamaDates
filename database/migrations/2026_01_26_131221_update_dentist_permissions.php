<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $role = \Illuminate\Support\Facades\DB::table('roles')->where('name', 'odontologo')->first();
        
        if ($role) {
            $perms = \Illuminate\Support\Facades\DB::table('permissions')
                ->whereIn('name', ['appointments.create', 'appointments.store'])
                ->pluck('id');

            foreach ($perms as $pId) {
                \Illuminate\Support\Facades\DB::table('permission_role')->updateOrInsert([
                    'role_id'       => $role->id,
                    'permission_id' => $pId,
                ]);
            }
        }
    }

    public function down(): void
    {
        // No revertimos para evitar quitar permisos que tal vez ya tenían
    }
};
