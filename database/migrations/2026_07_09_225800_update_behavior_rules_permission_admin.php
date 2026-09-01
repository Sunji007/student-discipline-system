<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Set CanAccess to false for 'ผู้ดูแลระบบ' for the 'behavior-rules' module
        DB::table('role_permissions')
            ->where('Role', 'ผู้ดูแลระบบ')
            ->where('ModuleName', 'behavior-rules')
            ->update(['CanAccess' => false]);
    }

    public function down(): void
    {
        // Revert (if needed)
        DB::table('role_permissions')
            ->where('Role', 'ผู้ดูแลระบบ')
            ->where('ModuleName', 'behavior-rules')
            ->update(['CanAccess' => true]);
    }
};
