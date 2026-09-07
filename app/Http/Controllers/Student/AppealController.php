<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use App\Models\BehaviorRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AppealController extends Controller
{
    public function index(Request $request)
    {
        $studentId = auth()->user()->student->StudentID;

        // ดึงรายการคำร้องทั้งหมดของนักเรียนคนนี้มาคำนวณสถิติ
        $allAppeals = Appeal::with(['behaviorRecord.rule'])
            ->where('StudentID', $studentId)
            ->get();

        $totalCount = $allAppeals->count();
        $restoredCount = $allAppeals->where('Status', 'คืนคะแนน')->count();
        $pendingCount = $allAppeals->filter(fn($i) => in_array($i->Status, ['รอตรวจสอบ', 'รอพิจารณา']))->count();
        $cancelledCount = $allAppeals->filter(fn($i) => in_array($i->Status, ['ยกเลิกคำร้อง', 'ยกเลิกคำร้องยื่นอุทธรณ์']))->count();
        
        $totalRestoredPoints = $allAppeals->where('Status', 'คืนคะแนน')->sum(function($a) {
            return $a->RestoredPoints ?? abs($a->behaviorRecord?->rule?->ScoreModifier ?? 0);
        });

        $restoredRate = $totalCount > 0 ? round(($restoredCount / $totalCount) * 100, 1) : 0;
        $pendingRate = $totalCount > 0 ? round(($pendingCount / $totalCount) * 100, 1) : 0;
        $cancelledRate = $totalCount > 0 ? round(($cancelledCount / $totalCount) * 100, 1) : 0;

        $stats = [
            'total' => $totalCount,
            'restored' => $restoredCount,
            'restored_rate' => $restoredRate,
            'pending' => $pendingCount,
            'pending_rate' => $pendingRate,
            'cancelled' => $cancelledCount,
            'cancelled_rate' => $cancelledRate,
            'total_restored_points' => $totalRestoredPoints,
        ];

        // สรุปสถิติแยกตามรายเดือน (สำหรับแสดงใน Modal สรุปสถิติ)
        $thMonths = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
            4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
            7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
            10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
        ];

        $monthlyStats = $allAppeals->groupBy(function($item) {
            $date = \Carbon\Carbon::parse($item->AppealDate ?? $item->created_at);
            return $date->format('Y-m');
        })->sortKeysDesc()->map(function($group, $ym) use ($thMonths) {
            $mTotal = $group->count();
            $mRestored = $group->where('Status', 'คืนคะแนน')->count();
            $mPending = $group->filter(fn($i) => in_array($i->Status, ['รอตรวจสอบ', 'รอพิจารณา']))->count();
            $mCancelled = $group->filter(fn($i) => in_array($i->Status, ['ยกเลิกคำร้อง', 'ยกเลิกคำร้องยื่นอุทธรณ์']))->count();
            $mRestoredPts = $group->where('Status', 'คืนคะแนน')->sum(function($a) {
                return $a->RestoredPoints ?? abs($a->behaviorRecord?->rule?->ScoreModifier ?? 0);
            });

            $year = (int)substr($ym, 0, 4);
            $month = (int)substr($ym, 5, 2);
            $monthName = ($thMonths[$month] ?? '') . ' ' . ($year + 543);

            return [
                'ym' => $ym,
                'month_name' => $monthName,
                'total' => $mTotal,
                'restored' => $mRestored,
                'restored_rate' => $mTotal > 0 ? round(($mRestored / $mTotal) * 100, 1) : 0,
                'pending' => $mPending,
                'pending_rate' => $mTotal > 0 ? round(($mPending / $mTotal) * 100, 1) : 0,
                'cancelled' => $mCancelled,
                'cancelled_rate' => $mTotal > 0 ? round(($mCancelled / $mTotal) * 100, 1) : 0,
                'restored_points' => $mRestoredPts,
            ];
        });

        // ดึงรายการอุทธรณ์ตามตัวกรองที่เลือก
        $appeals = Appeal::with(['behaviorRecord.rule'])
            ->where('StudentID', $studentId)
            ->when($request->filled('status'), function($q) use ($request) {
                if (in_array($request->status, ['ยกเลิกคำร้อง', 'ยกเลิกคำร้องยื่นอุทธรณ์'])) {
                    $q->whereIn('Status', ['ยกเลิกคำร้อง', 'ยกเลิกคำร้องยื่นอุทธรณ์']);
                } elseif (in_array($request->status, ['รอพิจารณา', 'รอตรวจสอบ'])) {
                    $q->whereIn('Status', ['รอตรวจสอบ', 'รอพิจารณา']);
                } else {
                    $q->where('Status', $request->status);
                }
            })
            ->orderBy('AppealDate', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('student.appeals.index', compact('appeals', 'stats', 'monthlyStats'));
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