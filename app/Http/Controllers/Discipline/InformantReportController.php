<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use App\Models\BehaviorRecord;
use App\Models\BehaviorRule;
use App\Models\InformantReport;
use App\Models\Message;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InformantReportController extends Controller
{
    public function index(Request $request)
    {
        $selectedSemesterId = $this->getSelectedSemesterId();
        $hasSemesterCol = \Illuminate\Support\Facades\Schema::hasColumn('informant_reports', 'semester_id');

        $query = InformantReport::query();

        if ($hasSemesterCol) {
            $query->where(function($q) use ($selectedSemesterId) {
                $q->where('semester_id', $selectedSemesterId)
                  ->orWhereNull('semester_id');
            });
        }

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        } else {
            $query->where('Status', '!=', 'จัดเก็บเข้าคลัง');
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('Description', 'like', '%' . $request->search . '%')
                  ->orWhere('Title', 'like', '%' . $request->search . '%')
                  ->orWhere('ReportID', 'like', '%' . $request->search . '%')
                  ->orWhere('StudentID', 'like', '%' . $request->search . '%');
            });
        }

        $reports = $query->with(['student', 'reporter.student'])
            ->orderBy('created_at', 'desc')
            ->orderBy('ReportID', 'desc')
            ->paginate(15);

        $counts = [
            'เรื่องใหม่'     => InformantReport::when($hasSemesterCol, fn($q) => $q->where(fn($q2) => $q2->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id')))->where('Status', 'เรื่องใหม่')->count(),
            'กำลังตรวจสอบ'  => InformantReport::when($hasSemesterCol, fn($q) => $q->where(fn($q2) => $q2->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id')))->where('Status', 'กำลังตรวจสอบ')->count(),
            'ปิดเรื่องแล้ว' => InformantReport::when($hasSemesterCol, fn($q) => $q->where(fn($q2) => $q2->where('semester_id', $selectedSemesterId)->orWhereNull('semester_id')))->where('Status', 'ปิดเรื่องแล้ว')->count(),
        ];

        return view('discipline.informant-reports.index', compact('reports', 'counts'));
    }

    public function show(InformantReport $informantReport)
    {
        $informantReport->load(['student', 'reporter.student']);

        // สถิติประวัติการแจ้งของผู้แจ้งรายนี้ (ทั้งหมดทุกภาคเรียน) เพื่อตรวจจับแพทเทิร์นแจ้งเท็จซ้ำ
        $reporterStats = null;
        if ($informantReport->ReporterID) {
            $byReporter = InformantReport::where('ReporterID', $informantReport->ReporterID);
            $total = (clone $byReporter)->count();
            $results = (clone $byReporter)
                ->whereNotNull('InvestigationResult')
                ->selectRaw('InvestigationResult, COUNT(*) as total')
                ->groupBy('InvestigationResult')
                ->pluck('total', 'InvestigationResult');

            $reporterStats = [
                'total'   => $total,
                'results' => $results,
                'maliciousCount' => (int) ($results[InformantReport::RESULT_MALICIOUS] ?? 0),
            ];
        }

        return view('discipline.informant-reports.show', compact('informantReport', 'reporterStats'));
    }

    // รับเรื่อง → เปลี่ยนเป็น "กำลังตรวจสอบ"
    public function accept(InformantReport $informantReport)
    {
        abort_if($informantReport->Status !== 'เรื่องใหม่', 422);

        $informantReport->update(['Status' => 'กำลังตรวจสอบ']);

        return back()->with('success', 'รับเรื่องแจ้งเบาะแสเรียบร้อยแล้ว กำลังดำเนินการตรวจสอบ');
    }

    // ปิดเรื่อง พร้อมบันทึกผลการตรวจสอบ
    public function close(Request $request, InformantReport $informantReport)
    {
        abort_if($informantReport->Status === 'ปิดเรื่องแล้ว', 422);

        $validated = $request->validate([
            'InvestigationResult' => ['required', 'in:' . implode(',', InformantReport::resultOptions())],
            'Remarks' => ['nullable', 'string', 'max:2000'],
            'CreateBehaviorRecord' => ['nullable', 'boolean'],
            'NotifyInvolved' => ['nullable', 'boolean'],
        ], [
            'InvestigationResult.required' => 'กรุณาเลือกผลการตรวจสอบก่อนปิดเรื่อง',
            'InvestigationResult.in' => 'ผลการตรวจสอบไม่ถูกต้อง',
            'Remarks.max' => 'หมายเหตุต้องมีความยาวไม่เกิน 2000 ตัวอักษร',
        ]);

        $informantReport->update([
            'Status' => 'ปิดเรื่องแล้ว',
            'InvestigationResult' => $validated['InvestigationResult'],
            'Remarks' => $request->input('Remarks'),
        ]);

        $extraNotes = [];

        // ผู้แจ้งเท็จโดยเจตนา → บันทึกพฤติกรรมหักคะแนนผู้แจ้ง (ถ้าเลือกและผู้แจ้งเป็นนักเรียน)
        if ($validated['InvestigationResult'] === InformantReport::RESULT_MALICIOUS
            && $request->boolean('CreateBehaviorRecord')
            && $informantReport->ReporterID) {
            $record = $this->createFalseReportBehaviorRecord($informantReport);
            if ($record) {
                $extraNotes[] = 'บันทึกพฤติกรรมหักคะแนนผู้แจ้งเรียบร้อยแล้ว (รหัสบันทึก ' . $record->RecordID . ' สถานะรออนุมัติ)';
            } else {
                $extraNotes[] = 'ไม่สามารถบันทึกพฤติกรรมได้ เนื่องจากผู้แจ้งไม่ใช่นักเรียนในระบบ';
            }
        }

        // ปิดเรื่องโดยผลไม่เป็นความจริง → แจ้งผู้ถูกกล่าวหาผ่านระบบข้อความเพื่อปกป้องเครดิตของเขา
        if ($request->boolean('NotifyInvolved')
            && $validated['InvestigationResult'] !== InformantReport::RESULT_TRUTH) {
            $notified = $this->notifyInvolvedStudents($informantReport);
            if ($notified > 0) {
                $extraNotes[] = "ส่งข้อความแจ้งผลให้ผู้เกี่ยวข้องแล้ว {$notified} คน";
            }
        }

        $message = 'ปิดเรื่องแจ้งเบาะแสเรียบร้อยแล้ว (ผลการตรวจสอบ: ' . $validated['InvestigationResult'] . ')';
        if (!empty($extraNotes)) {
            $message .= ' — ' . implode(' | ', $extraNotes);
        }

        return back()->with('success', $message);
    }

    // สร้างบันทึกพฤติกรรม "แจ้งข้อมูลเท็จ" ให้ผู้แจ้ง (สถานะรออนุมัติตาม workflow ปกติ)
    protected function createFalseReportBehaviorRecord(InformantReport $report): ?BehaviorRecord
    {
        $reporterStudent = Student::where('UserID', $report->ReporterID)->first();
        if (!$reporterStudent) {
            return null;
        }

        $rule = BehaviorRule::find('f4a7c2d9-8e3b-4c6a-9d5e-1b2f3a4c5d6e');
        if (!$rule) {
            return null;
        }

        return DB::transaction(function () use ($report, $reporterStudent, $rule) {
            return BehaviorRecord::create([
                'RecordID'    => Str::uuid()->toString(),
                'StudentID'   => $reporterStudent->StudentID,
                'RecordedBy'  => auth()->id(),
                'RuleID'      => $rule->RuleID,
                'Description' => 'แจ้งเบาะแสเท็จเรื่อง ' . $report->ReportID . ' (หัวข้อ: ' . \Illuminate\Support\Str::limit($report->Title, 60) . ') — ตรวจสอบโดยฝ่ายปกครอง ผลการตรวจสอบ: แจ้งเท็จโดยเจตนา',
                'RecordDate'  => now(),
                'Status'      => 'รออนุมัติ',
                'semester_id' => $this->getSelectedSemesterId(),
            ]);
        });
    }

    // ส่งข้อความแจ้งผู้ถูกกล่าวหาว่าเรื่องปิดแล้วและไม่มีผลต่อประวัติของเขา (ไม่เปิดเผยตัวผู้แจ้ง)
    protected function notifyInvolvedStudents(InformantReport $report): int
    {
        $students = $report->involved_students;
        if ($students->isEmpty()) {
            return 0;
        }

        $resultText = match ($report->InvestigationResult) {
            InformantReport::RESULT_INSUFFICIENT => 'ตรวจสอบแล้วไม่พบหลักฐานเพียงพอ',
            InformantReport::RESULT_HONEST_MISTAKE => 'ตรวจสอบแล้วพบว่าเป็นความเข้าใจผิดของผู้แจ้ง',
            InformantReport::RESULT_MALICIOUS => 'ตรวจสอบแล้วพบว่าเป็นการแจ้งข้อมูลเท็จ และฝ่ายปกครองได้ดำเนินการกับผู้แจ้งตามระเบียบเรียบร้อยแล้ว',
            default => 'ได้รับการตรวจสอบและปิดเรื่องแล้ว',
        };

        $content = "เรื่องแจ้งเบาะแสรหัส {$report->ReportID} ที่มีชื่อของคุณเกี่ยวข้อง ได้รับการตรวจสอบโดยฝ่ายปกครองเรียบร้อยแล้ว\n\n"
            . "ผลการตรวจสอบ: {$resultText}\n\n"
            . "การตรวจสอบครั้งนี้ไม่มีผลต่อคะแนนพฤติกรรมและประวัติของคุณแต่อย่างใด\n"
            . "หากมีข้อสงสัยสามารถติดต่อสอบถามฝ่ายปกครองได้ตลอดเวลา";

        $count = 0;
        foreach ($students as $student) {
            if (!$student->UserID) {
                continue;
            }
            Message::create([
                'MessageID'  => Str::uuid()->toString(),
                'SenderID'   => auth()->id(),
                'ReceiverID' => $student->UserID,
                'Content'    => $content,
                'SentDate'   => now(),
                'IsRead'     => false,
            ]);
            $count++;
        }

        return $count;
    }

    // ย้ายเรื่องเข้าคลังประวัติ (Archive)
    public function archive($id)
    {
        $informantReport = InformantReport::findOrFail($id);
        $informantReport->update(['Status' => 'จัดเก็บเข้าคลัง']);

        return back()->with('success', 'ย้ายเรื่องแจ้งเบาะแสเข้าคลังประวัติเรียบร้อยแล้ว');
    }

    // นำเรื่องออกจากคลังประวัติ (Unarchive)
    public function unarchive($id)
    {
        $informantReport = InformantReport::findOrFail($id);
        $informantReport->update(['Status' => 'ปิดเรื่องแล้ว']);

        return back()->with('success', 'นำเรื่องออกจากคลังประวัติเรียบร้อยแล้ว');
    }

    // ย้ายเรื่องที่ปิดแล้วทั้งหมดเข้าคลังประวัติ (Bulk Archive)
    public function bulkArchive()
    {
        $count = InformantReport::where('Status', '!=', 'จัดเก็บเข้าคลัง')->update(['Status' => 'จัดเก็บเข้าคลัง']);

        return back()->with('success', "ย้ายเรื่องแจ้งเบาะแสเข้าคลังประวัติเรียบร้อยแล้ว จำนวน {$count} เรื่อง");
    }

    // สร้างเรื่องใหม่ (ฝ่ายปกครองเพิ่มเองได้)
    public function create()
    {
        return view('discipline.informant-reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Description' => 'required|string|min:10',
            'evidence'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $evidencePath = null;
        if ($request->hasFile('evidence')) {
            $evidencePath = $request->file('evidence')
                ->store('informant-reports/evidence', 'public');
        }

        InformantReport::create([
            'ReportID'     => Str::uuid(),
            'Description'  => $validated['Description'],
            'EvidencePath' => $evidencePath,
            'ReportDate'   => now(),
            'Status'       => 'เรื่องใหม่',
        ]);

        return redirect()->route('discipline.informant-reports.index')
            ->with('success', 'บันทึกเรื่องแจ้งเบาะแสเรียบร้อยแล้ว');
    }

    public function destroy(InformantReport $informantReport)
    {
        $informantReport->delete();
        return redirect()->route('discipline.informant-reports.index')
            ->with('success', 'ลบเรื่องเรียบร้อยแล้ว');
    }
}