<?php

namespace App\Http\Controllers\ParentGuardian;

use App\Http\Controllers\Controller;
use App\Models\BehaviorRecord;
use Illuminate\Http\Request;

class BehaviorRecordController extends Controller
{
    public function index(Request $request)
    {
        $students = auth()->user()->parentStudents;
        abort_if($students->isEmpty(), 404, 'ไม่พบข้อมูลบุตรหลาน');

        $selectedStudentId = session('selected_student_id', $students->first()->StudentID);
        $student = $students->firstWhere('StudentID', $selectedStudentId) ?? $students->first();

        $query = BehaviorRecord::with('rule')
            ->where('StudentID', $student->StudentID)
            ->where('semester_id', $this->getSelectedSemesterId());

        if ($request->filled('type')) {
            $query->whereHas('rule', fn($q) =>
                $q->where('RuleType', $request->type)
            );
        }

        $records = $query->orderBy('RecordDate', 'desc')->paginate(20);

        return view('parent.behavior-records.index', compact('student', 'students', 'records'));
    }
}