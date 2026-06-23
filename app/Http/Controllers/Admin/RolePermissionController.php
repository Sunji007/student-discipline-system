<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RolePermissionController extends Controller
{
    // รายชื่อ Module ทั้งหมดในระบบ
    private array $modules = [
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

    private array $roles = [
        'ผู้ดูแลระบบ',
        'ฝ่ายปกครอง',
        'ครู',
        'นักเรียน',
        'ผู้ปกครอง',
    ];

    public function index()
    {
        // จัดเรียงเป็น matrix: Role × Module
        $permissions = RolePermission::all()
            ->groupBy('Role')
            ->map(fn($rows) => $rows->keyBy('ModuleName'));

        return view('admin.permissions.index', [
            'permissions' => $permissions,
            'roles'       => $this->roles,
            'modules'     => $this->modules,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'permissions'     => 'nullable|array',
            'permissions.*.*' => 'boolean',
        ]);

        $submitted = $request->input('permissions', []);

        // ลบ permissions เดิมทั้งหมดแล้วเขียนใหม่
        RolePermission::truncate();

        foreach ($this->roles as $role) {
            foreach ($this->modules as $module) {
                RolePermission::create([
                    'PermissionID' => Str::uuid(),
                    'Role'         => $role,
                    'ModuleName'   => $module,
                    'CanAccess'    => isset($submitted[$role][$module]),
                ]);
            }
        }

        return back()->with('success', 'บันทึกการตั้งค่าสิทธิ์เรียบร้อยแล้ว');
    }
}