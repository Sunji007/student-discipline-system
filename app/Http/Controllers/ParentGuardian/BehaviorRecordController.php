<?php

namespace App\Http\Controllers\ParentGuardian;

use App\Http\Controllers\Controller;
use App\Models\BehaviorRecord;
use Illuminate\Http\Request;

class BehaviorRecordController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->parentGuardian?->student;

        abort_if(!$student, 404, 'ไม่พบข้อมูลบุตรหลาน');

        $query = BehaviorRecord::with('rule')
            ->where('StudentID', $student->StudentID);

        if ($request->filled('type')) {
            $query->whereHas('rule', fn($q) =>
                $q->where('RuleType', $request->type)
            );
        }

        $records = $query->orderBy('RecordDate', 'desc')->paginate(20);

        return view('parent.behavior-records.index', compact('student', 'records'));
    }
}