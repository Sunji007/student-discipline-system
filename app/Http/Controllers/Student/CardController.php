<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CardController extends Controller
{
    public function show(Request $request): View
    {
        $student = $request->user()->student;
        abort_unless($student, 404, 'ไม่พบข้อมูลนักเรียน');

        return view('students.card', [
            'student' => $student,
            'backUrl' => route('student.dashboard'),
        ]);
    }
}
