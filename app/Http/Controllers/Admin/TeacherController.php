<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::query()->with('user');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('user', function ($uq) use ($request) {
                    $uq->where('FullName', 'like', '%' . $request->search . '%');
                })
                ->orWhere('TeacherID', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('department')) {
            $query->where('Department', $request->department);
        }

        $teachers = $query->paginate(15);

        return view('admin.teachers.index', compact('teachers'));
    }

    public function create()
    {
        return view('admin.teachers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'TeacherID'    => 'required|string|max:10|unique:teachers,TeacherID',
            'FullName'     => 'required|string|max:100',
            'Department'   => 'nullable|string|max:100',
            'AdvisoryRoom' => 'nullable|string|max:10',
        ], [
            'TeacherID.unique' => 'รหัสครูนี้มีอยู่ในระบบแล้ว',
        ]);

        // Create User Account first
        $user = User::create([
            'UserID'   => (string) Str::uuid(),
            'Username' => $validated['TeacherID'],
            'Password' => Hash::make($validated['TeacherID']), // Default password is TeacherID
            'FullName' => $validated['FullName'],
            'Role'     => 'ครู',
            'Status'   => 'ปกติ',
        ]);

        Teacher::create([
            'TeacherID'    => $validated['TeacherID'],
            'UserID'       => $user->UserID,
            'Department'   => $validated['Department'] ?? null,
            'AdvisoryRoom' => $validated['AdvisoryRoom'] ?? null,
        ]);

        return redirect()->route('admin.teachers.index')
            ->with('success', 'เพิ่มข้อมูลครูเรียบร้อยแล้ว (รหัสผ่านเริ่มต้นคือรหัสครู)');
    }

    public function edit($id)
    {
        $teacher = Teacher::findOrFail($id);
        return view('admin.teachers.edit', compact('teacher'));
    }

    public function update(Request $request, $id)
    {
        $teacher = Teacher::findOrFail($id);

        $validated = $request->validate([
            'FullName'     => 'required|string|max:100',
            'Department'   => 'nullable|string|max:100',
            'AdvisoryRoom' => 'nullable|string|max:10',
        ]);

        $teacher->Department   = $validated['Department'] ?? null;
        $teacher->AdvisoryRoom = $validated['AdvisoryRoom'] ?? null;
        $teacher->save();

        if ($teacher->user) {
            $teacher->user->FullName = $validated['FullName'];
            $teacher->user->save();
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', 'แก้ไขข้อมูลครูเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);

        // Delete associated user account
        if ($teacher->user) {
            $teacher->user->delete();
        } else {
            $teacher->delete();
        }

        return redirect()->route('admin.teachers.index')
            ->with('success', 'ลบข้อมูลครูเรียบร้อยแล้ว');
    }
}
