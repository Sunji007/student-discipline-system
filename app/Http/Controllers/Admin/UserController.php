<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Teacher;
use App\Models\DisciplineStaff;
use App\Models\Student;
use App\Models\ParentGuardian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('Role', $request->role);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('FullName', 'like', '%' . $request->search . '%')
                  ->orWhere('Username', 'like', '%' . $request->search . '%');
            });
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Username'       => 'required|string|max:50|unique:users,Username|regex:/^[a-zA-Z0-9]+$/',
            'Password'       => 'required|string|min:6|regex:/^[a-zA-Z0-9]+$/',
            'FullName'       => 'required|string|max:100',
            'Role'           => 'required|in:ฝ่ายปกครอง,ผู้ดูแลระบบ,ครู,นักเรียน,ผู้ปกครอง',
            'Status'         => 'required|in:ปกติ,ระงับการใช้งาน',
            'AdditionalInfo' => 'nullable|string|max:255',
            // ฟิลด์เพิ่มเติมตาม Role
            'TeacherID'      => 'nullable|required_if:Role,ครู|string|max:10|unique:teachers,TeacherID',
            'Department'     => 'nullable|string|max:100',
            'AdvisoryRoom'   => 'nullable|string|max:10',
            'Position'       => 'nullable|string|max:100',
            'Level'          => 'nullable|in:บันทึกได้,อนุมัติผล/ตั้งค่า',
            'StudentID'      => 'nullable|string|max:10',
            'GradeLevel'     => 'nullable|string|max:10',
            'Classroom'      => 'nullable|string|max:10',
        ], [
            'Username.regex'  => 'Username ใช้ได้เฉพาะตัวอักษรภาษาอังกฤษ (a-z, A-Z) และตัวเลข (0-9) เท่านั้น',
            'Username.unique' => 'Username นี้ถูกใช้งานแล้ว',
            'Password.regex'  => 'Password ใช้ได้เฉพาะตัวอักษรภาษาอังกฤษ (a-z, A-Z) และตัวเลข (0-9) เท่านั้น',
        ]);

        $user = User::create([
            'UserID'         => Str::uuid(),
            'Username'       => $validated['Username'],
            'Password'       => Hash::make($validated['Password']),
            'FullName'       => $validated['FullName'],
            'Role'           => $validated['Role'],
            'Status'         => $validated['Status'],
            'AdditionalInfo' => $validated['AdditionalInfo'] ?? null,
        ]);

        $classroom = $validated['Classroom'] ?? null;
        if ($classroom && !str_contains($classroom, '/')) {
            $classroom = ($validated['GradeLevel'] ?? '') . '/' . $classroom;
        }

        // Normalize AdvisoryRoom: strip "ม." prefix before saving
        $advisoryRoom = $validated['AdvisoryRoom'] ?? null;
        if ($advisoryRoom) {
            $advisoryRoom = preg_replace('/^ม\./', '', trim($advisoryRoom));
        }

        // สร้าง profile ตาม Role
        match ($validated['Role']) {
            'ครู' => Teacher::create([
                'TeacherID'    => $validated['TeacherID'],
                'UserID'       => $user->UserID,
                'Department'   => $validated['Department'] ?? null,
                'AdvisoryRoom' => $advisoryRoom,
            ]),
            'ฝ่ายปกครอง' => DisciplineStaff::create([
                'StaffID'  => Str::uuid(),
                'UserID'   => $user->UserID,
                'Position' => $validated['Position'] ?? null,
                'Level'    => $validated['Level'] ?? 'บันทึกได้',
            ]),
            'นักเรียน' => Student::create([
                'StudentID'    => $validated['StudentID'],
                'UserID'       => $user->UserID,
                'FullName'     => $validated['FullName'],
                'GradeLevel'   => $validated['GradeLevel'] ?? null,
                'Classroom'    => $classroom,
                'BehaviorScore'=> 100,
                'RiskStatus'   => 'ปกติ',
            ]),
            default => null,
        };

        return redirect()->route('admin.users.index')
            ->with('success', 'เพิ่มผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->merge(['Role' => $user->Role]);

        $validated = $request->validate([
            'FullName'       => 'required|string|max:100',
            'Role'           => 'required|in:ฝ่ายปกครอง,ผู้ดูแลระบบ,ครู,นักเรียน,ผู้ปกครอง',
            'Status'         => 'required|in:ปกติ,ระงับการใช้งาน',
            'AdditionalInfo' => 'nullable|string|max:255',
            'Password'       => 'nullable|string|min:6|regex:/^[a-zA-Z0-9]+$/',
            // Profile fields
            'Department'     => 'nullable|string|max:100',
            'AdvisoryRoom'   => 'nullable|string|max:10',
            'Position'       => 'nullable|string|max:100',
            'Level'          => 'nullable|in:บันทึกได้,อนุมัติผล/ตั้งค่า',
            'StudentID'      => 'nullable|required_if:Role,นักเรียน|string|max:10',
            'GradeLevel'     => 'nullable|string|max:10',
            'Classroom'      => 'nullable|string|max:10',
        ], [
            'Password.regex' => 'Password ใช้ได้เฉพาะตัวอักษรภาษาอังกฤษ (a-z, A-Z) และตัวเลข (0-9) เท่านั้น',
        ]);

        $classroom = $validated['Classroom'] ?? null;
        if ($classroom && !str_contains($classroom, '/')) {
            $classroom = ($validated['GradeLevel'] ?? '') . '/' . $classroom;
        }

        $user->FullName       = $validated['FullName'];
        $user->Role           = $validated['Role'];
        $user->Status         = $validated['Status'];
        $user->AdditionalInfo = $validated['AdditionalInfo'] ?? null;

        if (!empty($validated['Password'])) {
            $user->Password = Hash::make($validated['Password']);
        }

        $user->save();

        // Update or create role-specific profile
        // Normalize AdvisoryRoom: strip "ม." prefix before saving
        $advisoryRoomEdit = $validated['AdvisoryRoom'] ?? null;
        if ($advisoryRoomEdit) {
            $advisoryRoomEdit = preg_replace('/^ม\./', '', trim($advisoryRoomEdit));
        }

        if ($user->Role === 'ครู') {
            $user->teacher()->updateOrCreate(
                ['UserID' => $user->UserID],
                [
                    'TeacherID'    => optional($user->teacher)->TeacherID ?? substr($user->Username, 0, 10),
                    'Department'   => $validated['Department'] ?? null,
                    'AdvisoryRoom' => $advisoryRoomEdit,
                ]
            );
        } elseif ($user->Role === 'ฝ่ายปกครอง') {
            $user->disciplineOfficer()->updateOrCreate(
                ['UserID' => $user->UserID],
                [
                    'StaffID'  => optional($user->disciplineOfficer)->StaffID ?? (string) Str::uuid(),
                    'Position' => $validated['Position'] ?? null,
                    'Level'    => $validated['Level'] ?? 'บันทึกได้',
                ]
            );
        } elseif ($user->Role === 'นักเรียน') {
            $user->student()->updateOrCreate(
                ['UserID' => $user->UserID],
                [
                    'StudentID'    => $validated['StudentID'],
                    'FullName'     => $validated['FullName'],
                    'GradeLevel'   => $validated['GradeLevel'] ?? null,
                    'Classroom'    => $classroom,
                    'BehaviorScore'=> optional($user->student)->BehaviorScore ?? 100,
                    'RiskStatus'   => optional($user->student)->RiskStatus ?? 'ปกติ',
                ]
            );
        }

        // Clean up other profiles if role changed
        if ($user->Role !== 'ครู') {
            $user->teacher()->delete();
        }
        if ($user->Role !== 'ฝ่ายปกครอง') {
            $user->disciplineOfficer()->delete();
        }
        if ($user->Role !== 'นักเรียน') {
            $user->student()->delete();
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'แก้ไขข้อมูลผู้ใช้เรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'ลบผู้ใช้งานเรียบร้อยแล้ว');
    }
}