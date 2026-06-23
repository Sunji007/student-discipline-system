<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('FullName', 'like', '%' . $request->search . '%')
                  ->orWhere('StudentID', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('grade')) {
            $query->where('GradeLevel', $request->grade);
        }

        $students = $query->orderBy('StudentID')->paginate(15);

        return view('admin.students.index', compact('students'));
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'StudentID'  => 'required|string|max:10|unique:students,StudentID',
            'FullName'   => 'required|string|max:100',
            'GradeLevel' => 'required|string|max:10',
            'Classroom'  => 'required|string|max:10',
            'Gender'     => 'required|in:ชาย,หญิง',
            'Photo'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'StudentID.unique' => 'รหัสนักเรียนนี้มีอยู่ในระบบแล้ว',
        ]);

        // Create User Account first
        $user = User::create([
            'UserID'   => (string) Str::uuid(),
            'Username' => $validated['StudentID'],
            'FullName' => $validated['FullName'],
            'Password' => Hash::make($validated['StudentID']), // Default password is StudentID
            'Role'     => 'นักเรียน',
            'Status'   => 'ปกติ',
        ]);

        $photoPath = null;
        if ($request->hasFile('Photo')) {
            $photoPath = $request->file('Photo')->store('students', 'public');
        }

        $classroom = $validated['Classroom'];
        if (!str_contains($classroom, '/')) {
            $classroom = $validated['GradeLevel'] . '/' . $classroom;
        }

        Student::create([
            'StudentID'     => $validated['StudentID'],
            'UserID'        => $user->UserID,
            'FullName'      => $validated['FullName'],
            'GradeLevel'    => $validated['GradeLevel'],
            'Classroom'     => $classroom,
            'Gender'        => $validated['Gender'],
            'Photo'         => $photoPath,
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'เพิ่มนักเรียนใหม่เรียบร้อยแล้ว (รหัสผ่านเริ่มต้นคือรหัสนักเรียน)');
    }

    public function edit($id)
    {
        $student = Student::findOrFail($id);
        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'FullName'   => 'required|string|max:100',
            'GradeLevel' => 'required|string|max:10',
            'Classroom'  => 'required|string|max:10',
            'Gender'     => 'required|in:ชาย,หญิง',
            'Photo'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('Photo')) {
            // Delete old photo if exists
            if ($student->Photo) {
                Storage::disk('public')->delete($student->Photo);
            }
            $student->Photo = $request->file('Photo')->store('students', 'public');
        }

        $classroom = $validated['Classroom'];
        if (!str_contains($classroom, '/')) {
            $classroom = $validated['GradeLevel'] . '/' . $classroom;
        }

        $student->FullName   = $validated['FullName'];
        $student->GradeLevel = $validated['GradeLevel'];
        $student->Classroom  = $classroom;
        $student->Gender     = $validated['Gender'];
        $student->save();

        // Sync name to User model as well
        if ($student->user) {
            $student->user->FullName = $validated['FullName'];
            $student->user->save();
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'แก้ไขข้อมูลนักเรียนเรียบร้อยแล้ว');
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        // Delete photo if exists
        if ($student->Photo) {
            Storage::disk('public')->delete($student->Photo);
        }

        // Delete associated user account (will cascade delete student due to DB foreign key cascade)
        if ($student->user) {
            $student->user->delete();
        } else {
            $student->delete();
        }

        return redirect()->route('admin.students.index')
            ->with('success', 'ลบนักเรียนออกจากระบบเรียบร้อยแล้ว');
    }

    public function card($id)
    {
        $student = Student::findOrFail($id);
        return view('admin.students.card', compact('student'));
    }
}
