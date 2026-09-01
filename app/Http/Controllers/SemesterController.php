<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Semester;

class SemesterController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function switchSemester(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,semester_id'
        ]);

        session(['selected_semester_id' => $request->semester_id]);

        return redirect()->back()->with('success', 'สลับปีการศึกษา/ภาคเรียนสำเร็จ');
    }
}
