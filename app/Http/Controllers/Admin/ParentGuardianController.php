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
        return view('admin.parents.create', compact('student'));
    }

    /**
     * Store a new parent record.
     */
    public function store(Request $request, Student $student)
    {
        $data = $request->validate([
            'FullName'     => 'required|string|max:255',
            'Relationship' => 'required|string|max:100',
            'Phone'        => 'nullable|string|max:20',
            'Email'        => 'nullable|email|max:255',
            'Address'      => 'nullable|string|max:500',
        ]);

        $data['ParentID']  = 'PAR-' . Str::uuid();
        $data['StudentID'] = $student->StudentID;
        $data['UserID']    = null;

        ParentGuardian::create($data);

        return redirect()
            ->route('admin.students.parents.index', $student->StudentID)
            ->with('success', 'เพิ่มข้อมูลผู้ปกครองสำเร็จ');
    }

    /**
     * Show edit form for a parent.
     */
    public function edit(Student $student, ParentGuardian $parent)
    {
        return view('admin.parents.edit', compact('student', 'parent'));
    }

    /**
     * Update parent record.
     */
    public function update(Request $request, Student $student, ParentGuardian $parent)
    {
        $data = $request->validate([
            'FullName'     => 'required|string|max:255',
            'Relationship' => 'required|string|max:100',
            'Phone'        => 'nullable|string|max:20',
            'Email'        => 'nullable|email|max:255',
            'Address'      => 'nullable|string|max:500',
        ]);

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
}
