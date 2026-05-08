<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permissions = DB::table('roles')->where('is_admin', false)
            ->get()
            ->map(fn($role) => [
                'permission' => 'skin-api.skin',
                'role_id' => $role->id,
            ]);

        DB::table('permissions')->insertOrIgnore($permissions->all());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Ignore
    }
};
