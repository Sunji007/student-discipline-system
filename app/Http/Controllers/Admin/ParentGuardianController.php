<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentGuardian;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ParentGuardianController extends Controller
{
    /**
     * Show all parents of a student.
     */
    public function index(Student $student)
    {
        $parents = ParentGuardian::where('StudentID', $student->StudentID)->get();
        return view('admin.parents.index', compact('student', 'parents'));
    }

    /**
     * Show form to create a parent for a student.
     */
    public function create(Student $student)
    {
        $parentUsers = User::where('Role', 'ผู้ปกครอง')->get();
        return view('admin.parents.create', compact('student', 'parentUsers'));
    }

    /**
     * Store a new parent record.
     */
    public function store(Request $request, Student $student)
    {

        $data = $request->validate([
            'FirstName'    => 'required|string|max:50',
            'LastName'     => 'required|string|max:50',
            'Relationship' => 'required|string|max:100',
            'CitizenID'    => [
                'required',
                'string',
                'digits:13',
                \Illuminate\Validation\Rule::unique('parents', 'CitizenID')->where(function ($query) use ($student) {
                    return $query->where('StudentID', $student->StudentID);
                })
            ],
            'Phone'        => 'nullable|string|max:20|regex:/^\d{3}-\d{3}-\d{4}$/',
            'Email'        => 'required|email:rfc,dns|max:255',
            'Address'      => 'nullable|string|max:500',
            'UserID'       => 'nullable|string|exists:users,UserID',
        ], [
            'CitizenID.required' => 'กรุณากรอกรหัสบัตรประชาชน',
            'CitizenID.digits'   => 'รหัสบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
            'CitizenID.unique'   => 'ผู้ปกครองคนนี้ได้รับการเชื่อมโยงกับนักเรียนคนนี้อยู่แล้ว',
            'Phone.regex'        => 'เบอร์โทรศัพท์ต้องอยู่ในรูปแบบ 111-111-1111',
            'Email.required'     => 'กรุณากรอกอีเมล',
            'Email.email'        => 'รูปแบบอีเมลไม่ถูกต้อง หรือโดเมนอีเมลไม่มีอยู่จริง (เช่น @gmail.com)',
        ]);

        if ($request->filled('prefix') && isset($data['FirstName'])) {
            $data['FirstName'] = $request->prefix . $data['FirstName'];
        }

        $data['ParentID']  = (string) Str::uuid();
        $data['StudentID'] = $student->StudentID;

        $parent = ParentGuardian::create($data);
        $student->update(['ParentID' => $parent->ParentID]);

        return redirect()
            ->route('admin.students.parents.index', $student->StudentID)
            ->with('success', 'เพิ่มข้อมูลผู้ปกครองสำเร็จ');
    }

    /**
     * Show edit form for a parent.
     */
    public function edit(Student $student, ParentGuardian $parent)
    {
        $parentUsers = User::where('Role', 'ผู้ปกครอง')->get();
        return view('admin.parents.edit', compact('student', 'parent', 'parentUsers'));
    }

    /**
     * Update parent record.
     */
    public function update(Request $request, Student $student, ParentGuardian $parent)
    {
        $data = $request->validate([
            'FirstName'    => 'required|string|max:50',
            'LastName'     => 'required|string|max:50',
            'Relationship' => 'required|string|max:100',
            'CitizenID'    => [
                'required',
                'string',
                'digits:13',
                \Illuminate\Validation\Rule::unique('parents', 'CitizenID')->where(function ($query) use ($student) {
                    return $query->where('StudentID', $student->StudentID);
                })->ignore($parent->ParentID, 'ParentID')
            ],
            'Phone'        => 'nullable|string|max:20|regex:/^\d{3}-\d{3}-\d{4}$/',
            'Email'        => 'required|email:rfc,dns|max:255',
            'Address'      => 'nullable|string|max:500',
            'UserID'       => 'nullable|string|exists:users,UserID',
        ], [
            'CitizenID.required' => 'กรุณากรอกรหัสบัตรประชาชน',
            'CitizenID.digits'   => 'รหัสบัตรประชาชนต้องเป็นตัวเลข 13 หลัก',
            'CitizenID.unique'   => 'ผู้ปกครองคนนี้ได้รับการเชื่อมโยงกับนักเรียนคนนี้อยู่แล้ว',
            'Phone.regex'        => 'เบอร์โทรศัพท์ต้องอยู่ในรูปแบบ 111-111-1111',
            'Email.required'     => 'กรุณากรอกอีเมล',
            'Email.email'        => 'รูปแบบอีเมลไม่ถูกต้อง หรือโดเมนอีเมลไม่มีอยู่จริง (เช่น @gmail.com)',
        ]);

        if ($request->filled('prefix') && isset($data['FirstName'])) {
            $data['FirstName'] = $request->prefix . $data['FirstName'];
        }

        $parent->update($data);

        return redirect()
            ->route('admin.students.parents.index', $student->StudentID)
            ->with('success', 'แก้ไขข้อมูลผู้ปกครองสำเร็จ');
    }

    /**
     * Delete a parent record.
     */
    public function destroy(Student $student, ParentGuardian $parent)
    {
        $parent->delete();

        return redirect()
            ->route('admin.students.parents.index', $student->StudentID)
            ->with('success', 'ลบข้อมูลผู้ปกครองสำเร็จ');
    }

    public function checkCitizenID(Request $request)
    {
        $request->validate([
            'CitizenID' => 'required|string',
        ]);

        $parent = ParentGuardian::where('CitizenID', $request->CitizenID)->first();

        if ($parent) {
            $prefixes = ['นาย', 'นางสาว', 'นาง', 'ด.ช.', 'ด.ญ.'];
            $selectedPrefix = '';
            $firstNameOnly = $parent->FirstName;
            foreach ($prefixes as $p) {
                if (str_starts_with($parent->FirstName, $p)) {
                    $selectedPrefix = $p;
                    $firstNameOnly = substr($parent->FirstName, strlen($p));
                    break;
                }
            }

            return response()->json([
                'exists'       => true,
                'prefix'       => $selectedPrefix,
                'FirstName'    => $firstNameOnly,
                'LastName'     => $parent->LastName,
                'Phone'        => $parent->Phone,
                'Email'        => $parent->Email,
                'Address'      => $parent->Address,
                'UserID'       => $parent->UserID,
                'message'      => 'พบข้อมูลผู้ปกครองคนนี้ในระบบแล้ว ระบบได้กรอกข้อมูลให้อัตโนมัติ'
            ]);
        }

        $user = User::where('CitizenID', $request->CitizenID)->first();
        if ($user) {
            $prefixes = ['นาย', 'นางสาว', 'นาง', 'ด.ช.', 'ด.ญ.'];
            $selectedPrefix = '';
            $firstNameOnly = $user->FirstName;
            foreach ($prefixes as $p) {
                if (str_starts_with($user->FirstName, $p)) {
                    $selectedPrefix = $p;
                    $firstNameOnly = substr($user->FirstName, strlen($p));
                    break;
                }
            }

            return response()->json([
                'exists'       => true,
                'prefix'       => $selectedPrefix,
                'FirstName'    => $firstNameOnly,
                'LastName'     => $user->LastName,
                'Phone'        => $user->Phone,
                'Email'        => $user->Email,
                'UserID'       => $user->UserID,
                'message'      => 'พบข้อมูลผู้ใช้งานที่มีบัตรประชาชนนี้ในระบบแล้ว ระบบได้กรอกข้อมูลให้อัตโนมัติ'
            ]);
        }

        return response()->json([
            'exists' => false
        ]);
    }
}
