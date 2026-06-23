<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\BehaviorRecord;
use App\Models\Appeal;
use App\Models\Student;
use App\Models\BehaviorRule;
use App\Models\User;
use Carbon\Carbon;

class BehaviorRecordSeeder extends Seeder
{
    public function run(): void
    {
        $students      = Student::all();
        $deductRules   = BehaviorRule::where('RuleType', 'ตัดคะแนน')->get();
        $addRules      = BehaviorRule::where('RuleType', 'เพิ่มคะแนน')->get();
        $disciplineUser = User::where('Role', 'ฝ่ายปกครอง')->first();
        $teacherUser    = User::where('Role', 'ครู')->first();

        foreach ($students as $student) {
            // สุ่มบันทึกตัดคะแนน 2–4 รายการ
            $deductSample = $deductRules->random(min(3, $deductRules->count()));
            foreach ($deductSample as $i => $rule) {
                $recordId = Str::uuid();
                $status   = $i === 0 ? 'รออนุมัติ' : 'อนุมัติแล้ว';

                BehaviorRecord::create([
                    'RecordID'    => $recordId,
                    'StudentID'   => $student->StudentID,
                    'RecordedBy'  => $i % 2 === 0 ? $disciplineUser->UserID : $teacherUser->UserID,
                    'RuleID'      => $rule->RuleID,
                    'Description' => 'พบพฤติกรรม: ' . $rule->RuleName . ' บริเวณ' . collect(['หน้าโรงเรียน', 'ห้องเรียน', 'โรงอาหาร', 'สนาม'])->random(),
                    'RecordDate'  => Carbon::now()->subDays(rand(1, 60)),
                    'Penalty'     => collect(['ทำความสะอาด', 'แจ้งผู้ปกครอง', 'ทำงานสาธารณประโยชน์', null])->random(),
                    'Status'      => $status,
                ]);

                // บันทึกที่ 2 มีคำร้องโต้แย้ง
                if ($i === 1 && $student->BehaviorScore < 80) {
                    Appeal::create([
                        'AppealID'     => Str::uuid(),
                        'RecordID'     => $recordId,
                        'StudentID'    => $student->StudentID,
                        'Reason'       => 'ข้าพเจ้าไม่ได้กระทำการดังกล่าว เนื่องจากในวันเกิดเหตุข้าพเจ้าอยู่ที่ห้องสมุดตลอดช่วงเวลานั้น ขอให้ตรวจสอบกล้องวงจรปิดเพื่อยืนยัน',
                        'EvidencePath' => null,
                        'Status'       => collect(['รอตรวจสอบ', 'คืนคะแนน', 'ยกเลิกคำร้อง'])->random(),
                        'AppealDate'   => Carbon::now()->subDays(rand(1, 30)),
                    ]);

                    // อัปเดตสถานะ record ให้สอดคล้อง
                    BehaviorRecord::where('RecordID', $recordId)
                        ->update(['Status' => 'อยู่ในระหว่างโต้แย้ง']);
                }
            }

            // สุ่มบันทึกเพิ่มคะแนน 1–2 รายการ
            $addSample = $addRules->random(min(2, $addRules->count()));
            foreach ($addSample as $rule) {
                BehaviorRecord::create([
                    'RecordID'    => Str::uuid(),
                    'StudentID'   => $student->StudentID,
                    'RecordedBy'  => $disciplineUser->UserID,
                    'RuleID'      => $rule->RuleID,
                    'Description' => 'นักเรียนแสดงพฤติกรรมที่ดี: ' . $rule->RuleName,
                    'RecordDate'  => Carbon::now()->subDays(rand(1, 30)),
                    'Penalty'     => null,
                    'Status'      => 'อนุมัติแล้ว',
                ]);
            }
        }
    }
}