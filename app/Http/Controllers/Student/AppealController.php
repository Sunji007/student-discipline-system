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

    public function create(Request $request)
    {
        $studentId = auth()->user()->student->StudentID;
        $selectedRecordId = $request->query('record') ?: old('RecordID');

        // บันทึกที่อนุมัติแล้ว และยังไม่มีคำร้อง
        $records = BehaviorRecord::with('rule')
            ->where('StudentID', $studentId)
            ->whereIn('Status', ['อนุมัติ', 'อนุมัติแล้ว'])
            ->whereDoesntHave('appeal')
            ->orderBy('RecordDate', 'desc')
            ->get();

        $selectedRecord = null;
        if ($selectedRecordId) {
            $selectedRecord = $records->firstWhere('RecordID', $selectedRecordId)
                ?: BehaviorRecord::with('rule')->where('StudentID', $studentId)->where('RecordID', $selectedRecordId)->first();
        }

        return view('student.appeals.create', compact('records', 'selectedRecord', 'selectedRecordId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'RecordID' => 'required|exists:behavior_records,RecordID',
            'Reason'   => 'required|string',
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
        $record->update(['Status' => 'อยู่ในระหว่างยื่นอุทธรณ์']);

        return redirect()->route('student.appeals.index')
            ->with('success', 'ยื่นคำร้องโต้แย้งเรียบร้อยแล้ว รอการพิจารณา');
    }

    public function show(Appeal $appeal)
    {
        // ตรวจสอบว่าเป็นของนักเรียนคนนี้
        $studentId = auth()->user()->student->StudentID;
        abort_if($appeal->StudentID !== $studentId, 403);

        $appeal->load(['behaviorRecord.rule', 'behaviorRecord.recorder', 'reviewer']);
        return view('student.appeals.show', compact('appeal'));
    }

    public function cancel(Appeal $appeal)
    {
        // ตรวจสอบว่าเป็นของนักเรียนคนนี้
        $studentId = auth()->user()->student->StudentID;
        abort_if($appeal->StudentID !== $studentId, 403);

        // ตรวจสอบสถานะว่าต้องเป็น 'รอตรวจสอบ' เท่านั้น
        if ($appeal->Status !== 'รอตรวจสอบ') {
            return back()->with('error', 'ไม่สามารถยกเลิกคำร้องที่อยู่ในขั้นตอนพิจารณาแล้วได้');
        }

        \Illuminate\Support\Facades\DB::transaction(function () use ($appeal) {
            // ดึงพฤติกรรมที่เกี่ยวข้องกลับมาเป็นสถานะ 'อนุมัติแล้ว'
            if ($appeal->behaviorRecord) {
                $appeal->behaviorRecord->update(['Status' => 'อนุมัติแล้ว']);
            }

            // ลบไฟล์หลักฐาน (ถ้ามี)
            if ($appeal->EvidencePath) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($appeal->EvidencePath);
            }

            // ลบรายการคำร้องโต้แย้ง
            $appeal->delete();
        });

        return redirect()->route('student.appeals.index')
            ->with('success', 'ยกเลิกคำร้องโต้แย้งเรียบร้อยแล้ว');
    }
}