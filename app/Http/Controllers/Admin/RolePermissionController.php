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
                $canAccess = isset($submitted[$role][$module]);

                // บังคับสิทธิ์ความปลอดภัยในระดับ Server-side
                if ($role === 'ผู้ดูแลระบบ') {
                    // ผู้ดูแลระบบต้องเข้าถึงสิ่งจำเป็นได้เสมอ
                    if (in_array($module, ['dashboard', 'users', 'permissions'])) {
                        $canAccess = true;
                    }
                } else {
                    // บทบาทอื่นๆ ห้ามเข้าถึงหน้าจัดการผู้ใช้หรือตั้งค่าสิทธิ์เด็ดขาด
                    if (in_array($module, ['users', 'permissions'])) {
                        $canAccess = false;
                    }
                }

                RolePermission::create([
                    'PermissionID' => Str::uuid(),
                    'Role'         => $role,
                    'ModuleName'   => $module,
                    'CanAccess'    => $canAccess,
                ]);
            }
        }

        return back()->with('success', 'บันทึกการตั้งค่าสิทธิ์เรียบร้อยแล้ว');
    }
}