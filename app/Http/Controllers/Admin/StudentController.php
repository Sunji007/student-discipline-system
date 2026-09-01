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
        $grade = $request->input('grade'); // e.g. "ม.1"
        $classroom = $request->input('classroom'); // e.g. "1"

        if (!$grade || !$classroom) {
            return response()->json(['StudentID' => '']);
        }

        // 1. Year prefix: last 2 digits of Buddhist Era (e.g. 2569 -> 69)
        $yearPrefix = substr((string)((int)date('Y') + 543), -2);

        // 2. Grade digit: "ม.1" -> "1"
        $gradeDigit = preg_replace('/[^0-9]/', '', $grade);

        // 3. Classroom: "1" -> "01" (padded to 2 digits)
        $classroomPadded = str_pad(preg_replace('/[^0-9]/', '', $classroom), 2, '0', STR_PAD_LEFT);

        // Base search prefix: e.g. "69101"
        $prefix = $yearPrefix . $gradeDigit . $classroomPadded;

        // Find existing students whose StudentID starts with this prefix
        $count = Student::where('StudentID', 'like', $prefix . '%')->count();
        $nextSeq = str_pad($count + 1, 2, '0', STR_PAD_LEFT);

        $nextStudentId = $prefix . $nextSeq;

        return response()->json(['StudentID' => $nextStudentId]);
    }

    public function create()
    {
        $nextStudentId = '';
        return view('admin.students.create', compact('nextStudentId'));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'StudentID'  => 'required|string|max:10|unique:students,StudentID|regex:/^\d+$/',
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
            'Password'  => Hash::make("Student" . $validated['StudentID']), // e.g. Student10007 (secure mixed case + digits, no symbols)
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
            ->with('success', 'เพิ่มนักเรียนใหม่เรียบร้อยแล้ว (ชื่อผู้ใช้คือ ' . $stdUsername . ' รหัสผ่านเริ่มต้นคือ Student' . $validated['StudentID'] . ')');
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
        return view('admin.students.card', compact('student'));
    }
}
