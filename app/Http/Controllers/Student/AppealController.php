<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use App\Models\BehaviorRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppealController extends Controller
{
    public function index()
    {
        $studentId = auth()->user()->student->StudentID;
        $appeals = Appeal::with(['behaviorRecord.rule'])
            ->where('StudentID', $studentId)
            ->orderBy('AppealDate', 'desc')
            ->paginate(15);

        return view('student.appeals.index', compact('appeals'));
    }

    public function create()
    {
        $studentId = auth()->user()->student->StudentID;

        // บันทึกที่อนุมัติแล้ว และยังไม่มีคำร้อง
        $records = BehaviorRecord::with('rule')
            ->where('StudentID', $studentId)
            ->where('Status', 'อนุมัติแล้ว')
            ->whereDoesntHave('appeal')
            ->orderBy('RecordDate', 'desc')
            ->get();

        return view('student.appeals.create', compact('records'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'RecordID' => 'required|exists:behavior_records,RecordID',
            'Reason'   => 'required|string|min:20',
            'evidence' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $studentId = auth()->user()->student->StudentID;

        // ตรวจสอบว่าเป็นบันทึกของนักเรียนคนนี้
        $record = BehaviorRecord::where('RecordID', $validated['RecordID'])
            ->where('StudentID', $studentId)
            ->firstOrFail();

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')
                ->store('appeals/evidence', 'public');
        }

        Appeal::create([
            'AppealID'     => Str::uuid(),
            'RecordID'     => $record->RecordID,
            'StudentID'    => $studentId,
            'Reason'       => $validated['Reason'],
            'EvidencePath' => $evidencePath,
            'Status'       => 'รอตรวจสอบ',
            'AppealDate'   => now(),
        ]);

        // อัปเดตสถานะ record
        $record->update(['Status' => 'อยู่ในระหว่างโต้แย้ง']);

        return redirect()->route('student.appeals.index')
            ->with('success', 'ยื่นคำร้องโต้แย้งเรียบร้อยแล้ว รอการพิจารณา');
    }

    public function show(Appeal $appeal)
    {
        // ตรวจสอบว่าเป็นของนักเรียนคนนี้
        $studentId = auth()->user()->student->StudentID;
        abort_if($appeal->StudentID !== $studentId, 403);

        $appeal->load(['behaviorRecord.rule', 'behaviorRecord.recorder']);
        return view('student.appeals.show', compact('appeal'));
    }
}