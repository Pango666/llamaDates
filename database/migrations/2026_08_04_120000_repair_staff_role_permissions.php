<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('role_user')) {
            return;
        }

        $now = now();
        $dentistRoleId = DB::table('roles')->where('name', 'odontologo')->value('id');

        if ($dentistRoleId) {
            DB::table('users')
                ->where('role', 'odontologo')
                ->orderBy('id')
                ->pluck('id')
                ->each(function (int $userId) use ($dentistRoleId, $now): void {
                    DB::table('role_user')->insertOrIgnore([
                        'role_id' => $dentistRoleId,
                        'user_id' => $userId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                });
        }

        if (! Schema::hasTable('permissions') || ! Schema::hasTable('permission_role')) {
            return;
        }

        $assistantRoleId = DB::table('roles')->where('name', 'asistente')->value('id');
        $inventoryPermissionId = DB::table('permissions')->where('name', 'inventory.manage')->value('id');

        if ($assistantRoleId && $inventoryPermissionId) {
            DB::table('permission_role')->insertOrIgnore([
                'permission_id' => $inventoryPermissionId,
                'role_id' => $assistantRoleId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Data repair: do not remove role assignments that may be in active use.
    }
};
