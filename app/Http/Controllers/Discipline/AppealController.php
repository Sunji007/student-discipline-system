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
        $appeals = Appeal::with(['student', 'behaviorRecord.rule'])
            ->when($request->filled('status'), fn($q) => $q->where('Status', $request->status))
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
            'action' => 'required|in:คืนคะแนน,ยกเลิกคำร้อง',
        ]);

        DB::transaction(function () use ($request, $appeal) {
            $appeal->update(['Status' => $request->action]);

            if ($request->action === 'คืนคะแนน') {
                // คืน ScoreModifier กลับ
                $record   = $appeal->behaviorRecord;
                $modifier = $record->rule->ScoreModifier;

                // การคืนคะแนน = ทำสิ่งที่ตรงข้ามกับการบันทึกเดิม
                if ($record->rule->RuleType === 'ตัดคะแนน') {
                    $modifier = abs($modifier); // คืนคะแนนที่เคยตัด
                } else {
                    $modifier = -abs($modifier); // ลดคะแนนที่เคยเพิ่ม (หายาก)
                }

                $student = $appeal->student;
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

                // เปลี่ยนสถานะ record กลับ
                $record->update(['Status' => 'อนุมัติแล้ว']);
            }
        });

        $msg = $request->action === 'คืนคะแนน'
            ? 'คืนคะแนนให้นักเรียนเรียบร้อยแล้ว'
            : 'ยกเลิกคำร้องเรียบร้อยแล้ว';

        return redirect()->route('discipline.appeals.index')->with('success', $msg);
    }
}