<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ปรับค่า Classroom ในฐานข้อมูลให้ตรงกับระดับชั้นเสมอ ป้องกันกรณี ม.2/1/1
        $students = DB::table('students')->select('StudentID', 'GradeLevel', 'Classroom')->get();
        foreach ($students as $s) {
            $gradeNum = preg_replace('/[^0-9]/', '', $s->GradeLevel);
            if (!$gradeNum) continue;

            $parts = explode('/', (string) $s->Classroom);
            $roomNum = (int) preg_replace('/[^0-9]/', '', end($parts)) ?: 1;

            $targetClass = "{$gradeNum}/{$roomNum}";
            if ($s->Classroom !== $targetClass && $s->Classroom !== "ม.{$targetClass}") {
                DB::table('students')
                    ->where('StudentID', $s->StudentID)
                    ->update(['Classroom' => $targetClass]);
            }
        }
    }

    public function down(): void
    {
    }
};
