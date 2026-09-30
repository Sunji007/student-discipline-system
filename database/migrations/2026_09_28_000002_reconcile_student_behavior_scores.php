<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Student;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $activeSemester = DB::table('semesters')->where('is_active', true)->first();
        $activeSemId = $activeSemester ? $activeSemester->semester_id : 1;

        // 1. ปรับคะแนนนักเรียนที่ไม่มีประวัติพฤติกรรมในระบบเลย (Mock data จาก Seeder) ให้เป็น 100 เต็ม และสถานะปกติ
        DB::table('students')
            ->whereNotIn('StudentID', function ($query) {
                $query->select('StudentID')->from('behavior_records');
            })
            ->update([
                'BehaviorScore' => 100,
                'RiskStatus'    => 'ปกติ',
            ]);

        // 2. ปรับคะแนนนักเรียนที่มีประวัติพฤติกรรมจริง ให้ตรงกับผลการคำนวณสุทธิของภาคเรียน
        $studentsWithRecords = Student::whereIn('StudentID', function ($query) {
            $query->select('StudentID')->from('behavior_records');
        })->get();

        foreach ($studentsWithRecords as $student) {
            $calculatedScore = $student->getBehaviorScoreForSemester($activeSemId);
            $riskStatus = match (true) {
                $calculatedScore >= 80 => 'ปกติ',
                $calculatedScore >= 60 => 'ตักเตือน',
                default                => 'ทัณฑ์บน',
            };

            $student->update([
                'BehaviorScore' => $calculatedScore,
                'RiskStatus'    => $riskStatus,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
