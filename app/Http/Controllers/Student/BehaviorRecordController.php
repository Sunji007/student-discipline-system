<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BehaviorRecord;
use Illuminate\Http\Request;

class BehaviorRecordController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;

        $records = BehaviorRecord::with('rule')
            ->where('StudentID', $student->StudentID)
            ->when($request->filled('type'), fn($q) =>
                $q->whereHas('rule', fn($r) =>
                    $r->where('RuleType', $request->type)
                )
            )
            ->orderBy('RecordDate', 'desc')
            ->paginate(20);

        return view('student.behavior-records.index', compact('student', 'records'));
    }
}