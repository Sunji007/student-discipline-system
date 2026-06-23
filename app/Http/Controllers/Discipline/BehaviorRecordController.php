<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use App\Models\BehaviorRecord;
use App\Models\BehaviorRule;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BehaviorRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = BehaviorRecord::with(['student', 'rule', 'recorder']);

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('student', fn($q) =>
                $q->where('FullName', 'like', '%' . $request->search . '%')
                  ->orWhere('StudentID', 'like', '%' . $request->search . '%')
            );
        }

        $records = $query->orderBy('RecordDate', 'desc')->paginate(20);

        return view('discipline.behavior-records.index', compact('records'));
    }

    public function create()
    {
        $students = Student::orderBy('Classroom')->orderBy('FullName')->get();
        $rules    = BehaviorRule::orderBy('RuleType')->orderBy('Category')->get();

        return view('discipline.behavior-records.create', compact('students', 'rules'));
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
            'StudentID'   => $validated['StudentID'],
            'RecordedBy'  => auth()->user()->UserID,
            'RuleID'      => $validated['RuleID'],
            'Description' => $validated['Description'] ?? null,
            'RecordDate'  => $validated['RecordDate'],
            'Penalty'     => $validated['Penalty'] ?? null,
            'Status'      => 'รออนุมัติ',
        ]);

        return redirect()->route('discipline.behavior-records.index')
            ->with('success', 'บันทึกพฤติกรรมเรียบร้อยแล้ว รอการอนุมัติ');
    }

    public function show(BehaviorRecord $behaviorRecord)
    {
        $behaviorRecord->load(['student', 'rule', 'recorder', 'appeal']);
        return view('discipline.behavior-records.show', compact('behaviorRecord'));
    }

    // อนุมัติบันทึก → คำนวณคะแนนอัตโนมัติ
    public function approve(BehaviorRecord $record)
    {
        if ($record->Status !== 'รออนุมัติ') {
            return back()->with('error', 'ไม่สามารถอนุมัติรายการนี้ได้');
        }

        DB::transaction(function () use ($record) {
            // เปลี่ยนสถานะ
            $record->update(['Status' => 'อนุมัติแล้ว']);

            // ดึง ScoreModifier จากกฎ
            $modifier = $record->rule->ScoreModifier;

            // ถ้าเป็น ตัดคะแนน → modifier ต้องเป็นค่าลบ
            if ($record->rule->RuleType === 'ตัดคะแนน') {
                $modifier = -abs($modifier);
            } else {
                $modifier = abs($modifier);
            }

            // อัปเดตคะแนน + คำนวณ RiskStatus
            $student = $record->student;
            $newScore = max(0, min(100, $student->BehaviorScore + $modifier));

            $riskStatus = match(true) {
                $newScore >= 80 => 'ปกติ',
                $newScore >= 60 => 'เฝ้าระวัง',
                default         => 'วิกฤต',
            };

            $student->update([
                'BehaviorScore' => $newScore,
                'RiskStatus'    => $riskStatus,
            ]);
        });

        return back()->with('success', 'อนุมัติและปรับคะแนนนักเรียนเรียบร้อยแล้ว');
    }
}