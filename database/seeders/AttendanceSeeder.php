<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $teachers = User::where('Role', 'ครู')->get();

        // สร้างข้อมูลย้อนหลัง 30 วัน เฉพาะวันจันทร์–ศุกร์
        $dates = collect();
        $date  = Carbon::now()->subDays(30);
        while ($date->lte(Carbon::now())) {
            if ($date->isWeekday()) {
                $dates->push($date->format('Y-m-d'));
            }
            $date->addDay();
        }

        foreach ($students as $student) {
            // หาครูที่ดูแลห้องเดียวกัน
            $teacher = $teachers->first(fn($t) =>
                $t->teacher?->AdvisoryRoom === $student->Classroom
            ) ?? $teachers->first();

            foreach ($dates as $d) {
                // สุ่มสถานะ: มา 80%, สาย 12%, ขาด 8%
                $rand = rand(1, 100);
                $status = match(true) {
                    $rand <= 80 => 'มา',
                    $rand <= 92 => 'สาย',
                    default     => 'ขาด',
                };

                // นักเรียนกลุ่มเสี่ยงมีโอกาสขาดมากกว่า
                if ($student->RiskStatus === 'วิกฤต') {
                    $rand2 = rand(1, 100);
                    $status = match(true) {
                        $rand2 <= 55 => 'มา',
                        $rand2 <= 70 => 'สาย',
                        default      => 'ขาด',
                    };
                }

                Attendance::create([
                    'AttendanceID' => Str::uuid(),
                    'StudentID'    => $student->StudentID,
                    'RecordedBy'   => $teacher->UserID,
                    'Date'         => $d,
                    'Status'       => $status,
                ]);
            }
        }
    }
}