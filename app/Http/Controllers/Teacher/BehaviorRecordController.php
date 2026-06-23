<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\BehaviorRecord;
use App\Models\BehaviorRule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BehaviorRecordController extends Controller
{
    public function index()
    {
        $teacher  = auth()->user()->teacher;
        $records  = BehaviorRecord::with(['student', 'rule'])
            ->where('RecordedBy', auth()->user()->UserID)
            ->orderBy('RecordDate', 'desc')
            ->paginate(20);

        return view('teacher.behavior-records.index', compact('records'));
    }

    public function create()
    {
        $teacher  = auth()->user()->teacher;
        $students = Student::inAdvisoryRoom($teacher?->AdvisoryRoom)
            ->orderBy('FullName')->get();
        $rules    = BehaviorRule::orderBy('RuleType')->get();

        return view('teacher.behavior-records.create', compact('students', 'rules'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'StudentID'   => 'required|exists:students,StudentID',
            'RuleID'      => 'required|exists:behavior_rules,RuleID',
            'Description' => 'nullable|string',
            'RecordDate'  => 'required|date',
            'Penalty'     => 'nullable|string|max:100',
        ]);

        BehaviorRecord::create([
            'RecordID'    => Str::uuid(),
            'RecordedBy'  => auth()->user()->UserID,
            'Status'      => 'รออนุมัติ',
            ...$validated,
        ]);

        return redirect()->route('teacher.behavior-records.index')
            ->with('success', 'บันทึกพฤติกรรมเรียบร้อย รอฝ่ายปกครองอนุมัติ');
    }

    public function show(BehaviorRecord $behaviorRecord)
    {
        abort_if($behaviorRecord->RecordedBy !== auth()->user()->UserID, 403);
        $behaviorRecord->load(['student', 'rule', 'appeal']);
        return view('teacher.behavior-records.show', compact('behaviorRecord'));
    }
}