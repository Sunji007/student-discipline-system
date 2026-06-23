<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Student;

class ClassroomController extends Controller
{
    public function index()
    {
        $teacher  = auth()->user()->teacher;
        $students = Student::with('parent')
            ->inAdvisoryRoom($teacher?->AdvisoryRoom)
            ->orderBy('FullName')
            ->get();

        $classroom   = $teacher?->AdvisoryRoom;

        return view('teacher.classroom.index', compact('students', 'classroom'));
    }
}