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
     * @param int|null $evalSemesterId รหัสภาคเรียนที่ใช้ประเมินคะแนน (หากไม่ระบุ จะใช้ภาคเรียนก่อนหน้าสำหรับเทอม 1 หรือภาคเรียนปัจจุบัน)
     * @return array ข้อมูลสรุปผลการประมวลผล
     */
    public static function autoPromoteByBehaviorScore(int $minPassingScore = 50, ?int $evalSemesterId = null, ?int $targetAcademicYear = null): array
    {
        $currentSemester = Semester::current();
        $targetYear = $targetAcademicYear ?? ($currentSemester ? (int) $currentSemester->academic_year : (int) (now()->year + 543));

        // ตรวจสอบจากฐานข้อมูลโดยตรง: หากปีการศึกษานี้เคยถูกประมวลผลเลื่อนชั้นไปแล้ว ให้ปฏิเสธการทำซ้ำทันที
        if (\App\Models\StudentPromotion::isYearPromoted($targetYear)) {
            return [
                'success'         => true,
                'already_promoted'=> true,
                'message'         => "ปีนี้ประมวลผลแล้ว (ปีการศึกษา {$targetYear} ได้ดำเนินการเลื่อนชั้นไปแล้ว)",
                'promoted_count'  => 0,
                'graduated_count' => 0,
                'retained_count'  => 0,
                'total'           => 0,
                'details'         => ['promoted' => [], 'graduated' => [], 'retained' => []],
            ];
        }

        if ($evalSemesterId) {
            $semesterId = $evalSemesterId;
        } else {
            if (!$currentSemester) {
                return ['promoted' => [], 'graduated' => [], 'retained' => []];
            }

            // ดึงภาคเรียนก่อนหน้าตามลำดับเวลาจริง (ต้องน้อยกว่าปีปัจจุบัน หรือปีเดียวกันแต่เทอมน้อยกว่า ห้ามเลือกเทอมในอนาคต)
            $prevSemester = Semester::where(function ($query) use ($currentSemester) {
                $query->where('academic_year', '<', $currentSemester->academic_year)
                      ->orWhere(function ($sub) use ($currentSemester) {
                          $sub->where('academic_year', '=', $currentSemester->academic_year)
                              ->where('term', '<', $currentSemester->term);
                      });
            })
            ->orderBy('academic_year', 'desc')
            ->orderBy('term', 'desc')
            ->first();

            $semesterId = ($currentSemester->term === 1 && $prevSemester)
                ? $prevSemester->semester_id
                : $currentSemester->semester_id;
        }

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
            // ดับเบิลเช็คอีกครั้งภายใน Transaction เพื่อป้องกัน Concurrency / Race condition
            if (\App\Models\StudentPromotion::isYearPromoted($targetYear)) {
                DB::rollBack();
                return [
                    'success'         => true,
                    'already_promoted'=> true,
                    'message'         => "ปีนี้ประมวลผลแล้ว (ปีการศึกษา {$targetYear} ได้ดำเนินการเลื่อนชั้นไปแล้ว)",
                    'promoted_count'  => 0,
                    'graduated_count' => 0,
                    'retained_count'  => 0,
                    'total'           => 0,
                    'details'         => ['promoted' => [], 'graduated' => [], 'retained' => []],
                ];
            }

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

            // บันทึกประวัติการเลื่อนชั้นลงฐานข้อมูลอย่างถาวรภายใน Transaction เดียวกัน
            if (\Illuminate\Support\Facades\Schema::hasTable('student_promotions')) {
                \App\Models\StudentPromotion::create([
                    'academic_year'         => $targetYear,
                    'semester_id'           => $semesterId,
                    'evaluated_semester_id' => $evalSemesterId,
                    'promoted_count'        => $promotedCount,
                    'graduated_count'       => $graduatedCount,
                    'retained_count'        => $retainedCount,
                    'promoted_at'           => now(),
                ]);
            }

            DB::commit();

            // บันทึกแคชหลังจากประมวลผลสำเร็จเรียบร้อยแล้วเท่านั้น
            cache()->forever("auto_promoted_academic_year_{$targetYear}", true);

            Log::info("StudentPromotionService: ประมวลผลเลื่อนชั้นอัตโนมัติสำเร็จสำหรับปีการศึกษา {$targetYear} - เลื่อนชั้น: {$promotedCount}, จบการศึกษา: {$graduatedCount}, ซ้ำชั้น: {$retainedCount}");

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
