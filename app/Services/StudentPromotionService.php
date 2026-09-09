<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentPromotionService
{
    /**
     * ประมวลผลเลื่อนชั้นปีการศึกษาอัตโนมัติทั้งโรงเรียน โดยใช้คะแนนความประพฤติเป็นเกณฑ์หลัก
     *
     * เกณฑ์การตัดสิน:
     * - คะแนนความประพฤติ >= 50 คะแนน:
     *   - ม.1 ถึง ม.5 -> เลื่อนชั้นขึ้น 1 ระดับในห้องเดิมอัตโนมัติ (เช่น ม.1/1 -> ม.2/1)
     *   - ม.6 -> สำเร็จการศึกษา (Status: 'สำเร็จการศึกษา')
     * - คะแนนความประพฤติ < 50 คะแนน:
     *   - ไม่ผ่านเกณฑ์วินัย -> ซ้ำชั้น (คงอยู่ระดับชั้นและห้องเดิม)
     *
     * @param int $minPassingScore เกณฑ์คะแนนความประพฤติขั้นต่ำ (ค่าเริ่มต้น 50 คะแนน)
     * @return array ข้อมูลสรุปผลการประมวลผล
     */
    public static function autoPromoteByBehaviorScore(int $minPassingScore = 50): array
    {
        $currentSemester = Semester::current();
        $semesterId = $currentSemester ? $currentSemester->semester_id : null;

        // ดึงนักเรียนทั้งหมดที่ยังศึกษาอยู่ (ยังไม่สำเร็จการศึกษา)
        $students = Student::whereHas('user', function ($q) {
                $q->where('Status', '!=', 'สำเร็จการศึกษา');
            })
            ->with('user')
            ->get();

        $promotedCount = 0;
        $graduatedCount = 0;
        $retainedCount = 0;
        $details = [
            'promoted'  => [],
            'graduated' => [],
            'retained'  => [],
        ];

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                // คำนวณคะแนนพฤติกรรมในภาคเรียน/ปีการศึกษาปัจจุบัน
                $score = $semesterId 
                    ? $student->getBehaviorScoreForSemester($semesterId) 
                    : ($student->BehaviorScore ?? 100);

                // ค้นหาระดับชั้นและห้องเรียนปัจจุบัน
                $gradeNum = (int) preg_replace('/[^0-9]/', '', $student->GradeLevel);
                
                // ดึงหมายเลขห้องเรียนตัวสุดท้าย (เช่น "1/1" -> 1, "ม.1/1" -> 1, "2/1" -> 1, "1" -> 1)
                $parts = explode('/', (string) $student->Classroom);
                $classNum = (int) preg_replace('/[^0-9]/', '', end($parts));
                if (!$classNum) {
                    $classNum = 1;
                }

                if (!$gradeNum) {
                    if (preg_match('/^(ม\.)?(\d)/', $student->Classroom, $m)) {
                        $gradeNum = (int) $m[2];
                    }
                }

                if (!$gradeNum) {
                    continue;
                }

                if ($score >= $minPassingScore) {
                    // ผ่านเกณฑ์คะแนนพฤติกรรม
                    if ($gradeNum >= 6) {
                        // นักเรียนชั้น ม.6 สำเร็จการศึกษา
                        if ($student->user) {
                            $student->user->update(['Status' => 'สำเร็จการศึกษา']);
                        }
                        $graduatedCount++;
                        $details['graduated'][] = [
                            'id'    => $student->StudentID,
                            'name'  => $student->FullName,
                            'score' => $score,
                        ];
                    } else {
                        // นักเรียนชั้น ม.1 - ม.5 เลื่อนชั้นขึ้น 1 ระดับ
                        $nextGradeNum = $gradeNum + 1;
                        $nextGrade = "ม.{$nextGradeNum}";
                        $nextClass = "{$nextGradeNum}/{$classNum}";

                        $student->update([
                            'GradeLevel' => $nextGrade,
                            'Classroom'  => $nextClass,
                        ]);

                        if ($student->user && $student->user->Status !== 'ปกติ') {
                            $student->user->update(['Status' => 'ปกติ']);
                        }

                        $promotedCount++;
                        $details['promoted'][] = [
                            'id'    => $student->StudentID,
                            'name'  => $student->FullName,
                            'from'  => "ม.{$gradeNum}/{$classNum}",
                            'to'    => "{$nextGrade}/{$classNum}",
                            'score' => $score,
                        ];
                    }
                } else {
                    // ไม่ผ่านเกณฑ์พฤติกรรม (< 50 คะแนน) -> ซ้ำชั้น (คงอยู่ห้องเดิม)
                    $retainedCount++;
                    $details['retained'][] = [
                        'id'     => $student->StudentID,
                        'name'   => $student->FullName,
                        'room'   => "ม.{$gradeNum}/{$classNum}",
                        'score'  => $score,
                        'reason' => "คะแนนพฤติกรรมไม่ผ่านเกณฑ์ ({$score} / {$minPassingScore} คะแนน)",
                    ];
                }
            }

            DB::commit();

            Log::info("StudentPromotionService: ประมวลผลเลื่อนชั้นอัตโนมัติสำเร็จ - เลื่อนชั้น: {$promotedCount}, จบการศึกษา: {$graduatedCount}, ซ้ำชั้น: {$retainedCount}");

            return [
                'success'         => true,
                'promoted_count'  => $promotedCount,
                'graduated_count' => $graduatedCount,
                'retained_count'  => $retainedCount,
                'total'           => $students->count(),
                'details'         => $details,
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("StudentPromotionService error: " . $e->getMessage());

            return [
                'success' => false,
                'error'   => $e->getMessage(),
            ];
        }
    }
}
