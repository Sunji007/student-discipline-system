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
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('FirstName', 'like', '%' . $search . '%')
                  ->orWhere('LastName', 'like', '%' . $search . '%')
                  ->orWhere('StudentID', 'like', '%' . $search . '%')
                  ->orWhere(\DB::raw("CONCAT(FirstName, ' ', LastName)"), 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('grade')) {
            $query->where('GradeLevel', $request->grade);
        }

        $students = $query->orderBy('StudentID')->paginate(15);

        return view('admin.students.index', compact('students'));
    }

    public function getNextId(Request $request)
    {
        return response()->json(['StudentID' => Student::generateNextStudentId()]);
    }

    public function create()
    {
        $nextStudentId = Student::generateNextStudentId();
        return view('admin.students.create', compact('nextStudentId'));
    }

    public function store(Request $request)
    {
        if (!$request->filled('StudentID')) {
            $request->merge(['StudentID' => Student::generateNextStudentId()]);
        }

        $validated = $request->validate([
            'StudentID'  => 'required|string|max:10|unique:students,StudentID|unique:users,Username|regex:/^\d+$/',
            'CitizenID'  => 'required|string|digits:13|unique:users,CitizenID',
            'FirstName'  => 'required|string|max:50',
            'LastName'   => 'required|string|max:50',
            'GradeLevel' => 'required|string|max:10',
            'Classroom'  => 'required|string|max:10',
            'Gender'     => 'required|in:ชาย,หญิง',
            'Photo'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'StudentID.unique' => 'รหัสนักเรียนนี้มีอยู่ในระบบแล้ว',
            'StudentID.regex'  => 'รหัสนักเรียนต้องเป็นตัวเลขเท่านั้น',
            'CitizenID.required' => 'กรุณากรอกรหัสบัตรประชาชน',
            'CitizenID.digits'   => 'รหัสบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
            'CitizenID.unique'   => 'รหัสบัตรประชาชนนี้ถูกใช้งานแล้ว',
        ]);

        if ($request->filled('prefix') && isset($validated['FirstName'])) {
            $validated['FirstName'] = $request->prefix . $validated['FirstName'];
        }

        $stdUsername = $validated['StudentID'];

        // Create User Account first (No special characters in auto-generated default password)
        $user = User::create([
            'UserID'    => (string) Str::uuid(),
            'Username'  => $stdUsername,
            'CitizenID' => $validated['CitizenID'],
            'FirstName' => $validated['FirstName'],
            'LastName'  => $validated['LastName'],
            'Password'  => Hash::make($validated['CitizenID']), // เลขประจำตัวประชาชน 13 หลัก
            'Role'      => 'นักเรียน',
            'Status'    => 'ปกติ',
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
            'FirstName'     => $validated['FirstName'],
            'LastName'      => $validated['LastName'],
            'GradeLevel'    => $validated['GradeLevel'],
            'Classroom'     => $classroom,
            'Gender'        => $validated['Gender'],
            'Photo'         => $photoPath,
            'BehaviorScore' => 100,
            'RiskStatus'    => 'ปกติ',
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', 'เพิ่มนักเรียนใหม่เรียบร้อยแล้ว (ชื่อผู้ใช้คือ ' . $stdUsername . ' รหัสผ่านเริ่มต้นคือ เลขประจำตัวประชาชน ' . $validated['CitizenID'] . ')');
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
            'FirstName'  => 'required|string|max:50',
            'LastName'   => 'required|string|max:50',
            'CitizenID'  => 'required|string|digits:13|unique:users,CitizenID,' . $student->UserID . ',UserID',
            'GradeLevel' => 'required|string|max:10',
            'Classroom'  => 'required|string|max:10',
            'Gender'     => 'required|in:ชาย,หญิง',
            'Photo'      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'CitizenID.required' => 'กรุณากรอกรหัสบัตรประชาชน',
            'CitizenID.digits'   => 'รหัสบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
            'CitizenID.unique'   => 'รหัสบัตรประชาชนนี้ถูกใช้งานแล้ว',
        ]);

        if ($request->filled('prefix') && isset($validated['FirstName'])) {
            $validated['FirstName'] = $request->prefix . $validated['FirstName'];
        }

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

        $student->FirstName  = $validated['FirstName'];
        $student->LastName   = $validated['LastName'];
        $student->GradeLevel = $validated['GradeLevel'];
        $student->Classroom  = $classroom;
        $student->Gender     = $validated['Gender'];
        $student->save();

        // Sync name to User model as well
        if ($student->user) {
            $student->user->FirstName = $validated['FirstName'];
            $student->user->LastName  = $validated['LastName'];
            $student->user->CitizenID  = $validated['CitizenID'];
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
        return view('students.card', [
            'student' => $student,
            'backUrl' => route('admin.students.index'),
        ]);
    }
}
