<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\RolePermission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            'dashboard',
            'behavior-records',
            'behavior-rules',
            'appeals',
            'attendance',
            'messages',
            'users',
            'permissions',
            'risk-students',
            'informant-reports',
        ];

        // Matrix: Role → [modules ที่เข้าถึงได้]
        $access = [
            'ผู้ดูแลระบบ' => [
                'dashboard', 'users', 'permissions',
                'behavior-rules', 'behavior-records',
                'appeals', 'attendance', 'messages',
                'risk-students', 'informant-reports',
            ],
            'ฝ่ายปกครอง' => [
                'dashboard', 'behavior-records', 'appeals',
                'attendance', 'messages', 'risk-students',
                'informant-reports',
            ],
            'ครู' => [
                'dashboard', 'behavior-records',
                'attendance', 'messages',
            ],
            'นักเรียน' => [
                'dashboard', 'behavior-records',
                'appeals', 'attendance', 'messages',
            ],
            'ผู้ปกครอง' => [
                'dashboard', 'behavior-records',
                'attendance', 'messages',
            ],
        ];

        $roles = array_keys($access);

        foreach ($roles as $role) {
            foreach ($modules as $module) {
                RolePermission::create([
                    'PermissionID' => Str::uuid(),
                    'Role'         => $role,
                    'ModuleName'   => $module,
                    'CanAccess'    => in_array($module, $access[$role]),
                ]);
            }
        }
    }
}