<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $existsAdmin = DB::table('role_permissions')
            ->where('Role', 'ผู้ดูแลระบบ')
            ->where('ModuleName', 'behavior-rules')
            ->exists();
        if (!$existsAdmin) {
            DB::table('role_permissions')->insert([
                'PermissionID' => (string) Str::uuid(),
                'Role' => 'ผู้ดูแลระบบ',
                'ModuleName' => 'behavior-rules',
                'CanAccess' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('role_permissions')
                ->where('Role', 'ผู้ดูแลระบบ')
                ->where('ModuleName', 'behavior-rules')
                ->update(['CanAccess' => false]);
        }

        $existsDiscipline = DB::table('role_permissions')
            ->where('Role', 'ฝ่ายปกครอง')
            ->where('ModuleName', 'behavior-rules')
            ->exists();
        if (!$existsDiscipline) {
            DB::table('role_permissions')->insert([
                'PermissionID' => (string) Str::uuid(),
                'Role' => 'ฝ่ายปกครอง',
                'ModuleName' => 'behavior-rules',
                'CanAccess' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('role_permissions')
                ->where('Role', 'ฝ่ายปกครอง')
                ->where('ModuleName', 'behavior-rules')
                ->update(['CanAccess' => true]);
        }
    }

    public function down(): void
    {
        DB::table('role_permissions')
            ->where('ModuleName', 'behavior-rules')
            ->whereIn('Role', ['ผู้ดูแลระบบ', 'ฝ่ายปกครอง'])
            ->update(['CanAccess' => false]);
    }
};
