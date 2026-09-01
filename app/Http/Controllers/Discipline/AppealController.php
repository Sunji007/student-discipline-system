<?php

namespace App\Http\Controllers\Discipline;

use App\Http\Controllers\Controller;
use App\Models\Appeal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AppealController extends Controller
{
    public function index(Request $request)
    {
        $selectedSemesterId = $this->getSelectedSemesterId();

        $appeals = Appeal::with(['student', 'behaviorRecord.rule'])
            ->whereHas('behaviorRecord', fn($q) => $q->where('semester_id', $selectedSemesterId))
            ->when($request->filled('status'), function($q) use ($request) {
                if (in_array($request->status, ['ยกเลิกคำร้อง', 'ยกเลิกคำร้องยื่นอุทธรณ์'])) {
                    $q->whereIn('Status', ['ยกเลิกคำร้อง', 'ยกเลิกคำร้องยื่นอุทธรณ์']);
                } else {
                    $q->where('Status', $request->status);
                }
            })
            ->orderBy('AppealDate', 'desc')
            ->paginate(20);

        return view('discipline.appeals.index', compact('appeals'));
    }

    public function show(Appeal $appeal)
    {
        $appeal->load(['student', 'behaviorRecord.rule', 'behaviorRecord.recorder']);
        return view('discipline.appeals.show', compact('appeal'));
    }

    // คืนคะแนน หรือ ยกเลิกคำร้อง
    public function resolve(Request $request, Appeal $appeal)
    {
        $request->validate([
            'action'          => 'required|in:คืนคะแนน,ยกเลิกคำร้อง',
            'restored_points' => 'nullable|numeric|min:1|max:100',
            'review_notes'    => 'nullable|string|max:500',
        ]);

        $statusToSave = $request->action === 'คืนคะแนน' ? 'คืนคะแนน' : 'ยกเลิกคำร้อง';

        DB::transaction(function () use ($request, $statusToSave, $appeal) {
            $pointsToReturn = null;
            if ($request->action === 'คืนคะแนน') {
                $record   = $appeal->behaviorRecord;
                $defaultPoints = abs($record->rule->ScoreModifier ?? 0);

                // ใช้คะแนนที่ฝ่ายปกครองระบุ หรือใช้คะแนนเต็มเดิมของกฎนั้น
                $pointsToReturn = $request->filled('restored_points')
                    ? floatval($request->restored_points)
                    : $defaultPoints;

                // การคืนคะแนน = เพิ่มคะแนนตามจำนวนที่กำหนดคืน
                if ($record->rule->RuleType === 'ตัดคะแนน') {
                    $modifier = abs($pointsToReturn);
                } else {
                    $modifier = -abs($pointsToReturn);
                }

                $student  = $appeal->student;
                $newScore = max(0, min(100, $student->BehaviorScore + $modifier));
                $riskStatus = match(true) {
                    $newScore >= 80 => 'ปกติ',
                    $newScore >= 60 => 'ตักเตือน',
                    default         => 'ทัณฑ์บน',
                };

                $student->update([
                    'BehaviorScore' => $newScore,
                    'RiskStatus'    => $riskStatus,
                ]);

                // เปลี่ยนสถานะ record
                $record->update(['Status' => 'อนุมัติแล้ว']);
            }

            $appeal->update([
                'Status'         => $statusToSave,
                'ReviewerID'     => auth()->id(),
                'ReviewDate'     => now(),
                'ReviewNotes'    => $request->input('review_notes'),
                'RestoredPoints' => $pointsToReturn,
            ]);
        });

        $restoredText = ($request->action === 'คืนคะแนน' && $request->filled('restored_points'))
            ? "จำนวน {$request->restored_points} คะแนน "
            : " ";

        $msg = $request->action === 'คืนคะแนน'
            ? "คืนคะแนน{$restoredText}ให้นักเรียนเรียบร้อยแล้ว"
            : 'ยกเลิกคำร้องเรียบร้อยแล้ว';

        return redirect()->route('discipline.appeals.index')->with('success', $msg);
    }
}