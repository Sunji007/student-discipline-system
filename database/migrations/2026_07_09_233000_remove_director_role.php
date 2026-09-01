<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Delete permissions for 'ผอ.'
        DB::table('role_permissions')
            ->where('Role', 'ผอ.')
            ->delete();

        // Revert any user with 'ผอ.' role back to 'ครู'
        DB::table('users')
            ->where('Role', 'ผอ.')
            ->update([
                'Role'       => 'ครู',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // No-op
    }
};
