<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\BehaviorRecord;
use App\Models\BehaviorRule;
use App\Models\Student;
use Illuminate\Support\Str;

class AttendanceDeductionService
{
    public const RULE_NAME = 'ขาดเข้าแถว/มาสายสะสมครบตามเกณฑ์';

    /**
     * Get or create the designated deduction rule
     */
    public function getDeductionRule(): BehaviorRule
    {
        return BehaviorRule::firstOrCreate(
            ['RuleName' => self::RULE_NAME],
            [
                'RuleID'        => (string) Str::uuid(),
                'RuleType'      => 'ตัดคะแนน',
                'Category'      => 'การเข้าเรียนและระเบียบสถานศึกษา',
                'ScoreModifier' => -5,
                'Description'   => 'ระบบตัดคะแนนอัตโนมัติเมื่อขาดเข้าแถวสะสมครบทุก 3 ครั้ง (มาสายสะสม 3 ครั้ง = ขาด 1 ครั้ง)',
            ]
        );
    }

    /**
     * Calculate effective absent count: absent + floor(late / 3)
     */
    public function calculateAttendanceStats(string $studentId, $semesterId = null): array
    {
        $query = Attendance::where('StudentID', $studentId);
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }
        $records = $query->get();

        $absentCount = $records->where('Status', 'ขาด')->count();
        $lateCount   = $records->where('Status', 'สาย')->count();
        $presentCount= $records->where('Status', 'มา')->count();

        // 3 lates = 1 absent
        $effectiveAbsent = $absentCount + intdiv($lateCount, 3);
        $expectedCycles  = intdiv($effectiveAbsent, 3);
        $remainder       = $effectiveAbsent % 3; // 0, 1, or 2 (towards next 3)

        return [
            'absent'          => $absentCount,
            'late'            => $lateCount,
            'present'         => $presentCount,
            'total'           => $records->count(),
            'effective_absent'=> $effectiveAbsent,
            'expected_cycles' => $expectedCycles,
            'deducted_points' => $expectedCycles * 5,
            'remainder'       => $remainder,
            'needed_for_next' => $remainder > 0 ? (3 - $remainder) : 3,
        ];
    }

    /**
     * Synchronize automatic deductions for a single student in a semester
     * Returns the net change in penalty records (+N if new deductions created, -N if refunded, 0 if unchanged)
     */
    public function syncStudentDeductions(string $studentId, $semesterId, string $recordedByUserId, string $recordDate): int
    {
        $stats = $this->calculateAttendanceStats($studentId, $semesterId);
        $expectedCycles = $stats['expected_cycles'];
        $rule = $this->getDeductionRule();

        // Get existing auto-deduction records for this student and semester
        $query = BehaviorRecord::where('StudentID', $studentId)
            ->where('RuleID', $rule->RuleID);
        if ($semesterId) {
            $query->where('semester_id', $semesterId);
        }
        
        $existingRecords = $query->orderBy('created_at', 'asc')->get();
        $currentCount = $existingRecords->count();
        $change = 0;

        if ($expectedCycles > $currentCount) {
            // Need to add new deduction record(s)
            $toAdd = $expectedCycles - $currentCount;
            for ($i = $currentCount + 1; $i <= $expectedCycles; $i++) {
                BehaviorRecord::create([
                    'RecordID'    => (string) Str::uuid(),
                    'StudentID'   => $studentId,
                    'RuleID'      => $rule->RuleID,
                    'RecordDate'  => $recordDate,
                    'Description' => "ตัดคะแนนอัตโนมัติรอบที่ {$i} (ขาดสะสม {$stats['absent']} ครั้ง, มาสาย {$stats['late']} ครั้ง ในภาคเรียน)",
                    'RecordedBy'  => $recordedByUserId,
                    'Status'      => 'อนุมัติแล้ว',
                    'semester_id' => $semesterId,
                ]);
            }
            $change = $toAdd;
        } elseif ($expectedCycles < $currentCount) {
            // Need to remove excess deduction record(s) if attendance was edited backwards
            $toRemove = $currentCount - $expectedCycles;
            $removable = $existingRecords->reverse()->filter(function ($rec) {
                return !$rec->appeal || $rec->appeal->Status === 'ยกเลิกคำร้อง';
            })->take($toRemove);

            foreach ($removable as $rec) {
                $rec->delete();
                $change--;
            }
        }

        // Recalculate student score if there was any change
        if ($change !== 0) {
            $student = Student::find($studentId);
            if ($student) {
                if (method_exists($student, 'getBehaviorScoreForSemester') && $semesterId) {
                    $student->BehaviorScore = $student->getBehaviorScoreForSemester($semesterId);
                }
                if (method_exists($student, 'getRiskStatusForSemester') && $semesterId) {
                    $student->RiskStatus = $student->getRiskStatusForSemester($semesterId);
                }
                $student->save();
            }
        }

        return $change;
    }
}
