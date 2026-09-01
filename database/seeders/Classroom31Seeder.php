<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentGuardian;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class Classroom31Seeder extends Seeder
{
    public static function seed40Students(): int
    {
        $students = [
            // ชาย 20 คน
            ['6930101', 'ด.ช.ปัญญา', 'เลิศล้ำ', 'ชาย', 100, 'ปกติ', 'นายประสงค์', 'เลิศล้ำ', 'บิดา', '5030101', '1909800301011'],
            ['6930102', 'ด.ช.กิตติศักดิ์', 'พรหมมินทร์', 'ชาย', 95, 'ปกติ', 'นายมนตรี', 'พรหมมินทร์', 'บิดา', '5030102', '1909800301022'],
            ['6930103', 'ด.ช.ชลันธร', 'วงศ์สว่าง', 'ชาย', 100, 'ปกติ', 'นายสมศักดิ์', 'วงศ์สว่าง', 'บิดา', '5030103', '1909800301033'],
            ['6930104', 'ด.ช.ณัฐพงษ์', 'สุขเกษม', 'ชาย', 85, 'ปกติ', 'นางมยุรี', 'สุขเกษม', 'มารดา', '5030104', '1909800301044'],
            ['6930105', 'ด.ช.ธนกฤต', 'เจริญสุข', 'ชาย', 100, 'ปกติ', 'นายธีรเดช', 'เจริญสุข', 'บิดา', '5030105', '1909800301055'],
            ['6930106', 'ด.ช.ธีรพัฒน์', 'รัตนวิชัย', 'ชาย', 90, 'ปกติ', 'นางวิไล', 'รัตนวิชัย', 'มารดา', '5030106', '1909800301066'],
            ['6930107', 'ด.ช.นรภัทร', 'บุญส่ง', 'ชาย', 75, 'เฝ้าระวัง', 'นายวินัย', 'บุญส่ง', 'บิดา', '5030107', '1909800301077'],
            ['6930108', 'ด.ช.ปวรุตม์', 'ศรีสวัสดิ์', 'ชาย', 100, 'ปกติ', 'นายสุรชัย', 'ศรีสวัสดิ์', 'บิดา', '5030108', '1909800301088'],
            ['6930109', 'ด.ช.พชร', 'เมธาพร', 'ชาย', 95, 'ปกติ', 'นางประภาพร', 'เมธาพร', 'มารดา', '5030109', '1909800301099'],
            ['6930110', 'ด.ช.ภูริณัฐ', 'ทองคำ', 'ชาย', 100, 'ปกติ', 'นายวิชัย', 'ทองคำ', 'บิดา', '5030110', '1909800301101'],
            ['6930111', 'ด.ช.ยศกร', 'ดำรงเกียรติ', 'ชาย', 60, 'วิกฤต', 'นางสมศรี', 'ดำรงเกียรติ', 'มารดา', '5030111', '1909800301112'],
            ['6930112', 'ด.ช.วรากร', 'พิทักษ์ไทย', 'ชาย', 100, 'ปกติ', 'นายอดิศักดิ์', 'พิทักษ์ไทย', 'บิดา', '5030112', '1909800301123'],
            ['6930113', 'ด.ช.ศุภกร', 'กิจเจริญ', 'ชาย', 90, 'ปกติ', 'นายประเสริฐ', 'กิจเจริญ', 'บิดา', '5030113', '1909800301134'],
            ['6930114', 'ด.ช.สหัสวรรษ', 'มั่นคง', 'ชาย', 80, 'เฝ้าระวัง', 'นางดวงใจ', 'มั่นคง', 'มารดา', '5030114', '1909800301145'],
            ['6930115', 'ด.ช.อนาวิล', 'สายสุวรรณ', 'ชาย', 100, 'ปกติ', 'นายสมหมาย', 'สายสุวรรณ', 'บิดา', '5030115', '1909800301156'],
            ['6930116', 'ด.ช.อัครวินท์', 'เพชรประดับ', 'ชาย', 95, 'ปกติ', 'นายไพโรจน์', 'เพชรประดับ', 'บิดา', '5030116', '1909800301167'],
            ['6930117', 'ด.ช.อิทธิพล', 'โสภณพานิช', 'ชาย', 100, 'ปกติ', 'นางกัญญา', 'โสภณพานิช', 'มารดา', '5030117', '1909800301178'],
            ['6930118', 'ด.ช.กฤติน', 'ภูมิทรัพย์', 'ชาย', 85, 'ปกติ', 'นายชวลิต', 'ภูมิทรัพย์', 'บิดา', '5030118', '1909800301189'],
            ['6930119', 'ด.ช.ฐากูร', 'พัฒนศิริ', 'ชาย', 100, 'ปกติ', 'นายธเนศ', 'พัฒนศิริ', 'บิดา', '5030119', '1909800301190'],
            ['6930120', 'ด.ช.พงศกร', 'มหาชัย', 'ชาย', 55, 'วิกฤต', 'นางรุ่งนภา', 'มหาชัย', 'มารดา', '5030120', '1909800301201'],

            // หญิง 20 คน
            ['6930121', 'ด.ญ.กมลวรรณ', 'นฤมิต', 'หญิง', 100, 'ปกติ', 'นางจินตนา', 'นฤมิต', 'มารดา', '5030121', '1909800301212'],
            ['6930122', 'ด.ญ.เขมจิรา', 'สุวรรณฉวี', 'หญิง', 100, 'ปกติ', 'นายอรรถพล', 'สุวรรณฉวี', 'บิดา', '5030122', '1909800301223'],
            ['6930123', 'ด.ญ.จริญญา', 'บูรณศิลป์', 'หญิง', 95, 'ปกติ', 'นางจริยา', 'บูรณศิลป์', 'มารดา', '5030123', '1909800301234'],
            ['6930124', 'ด.ญ.ชนัญชิดา', 'สดใส', 'หญิง', 100, 'ปกติ', 'นายประสิทธิ์', 'สดใส', 'บิดา', '5030124', '1909800301245'],
            ['6930125', 'ด.ญ.ชลธิชา', 'มีทรัพย์', 'หญิง', 90, 'ปกติ', 'นางวรรณา', 'มีทรัพย์', 'มารดา', '5030125', '1909800301256'],
            ['6930126', 'ด.ญ.ณิชากร', 'พลายงาม', 'หญิง', 100, 'ปกติ', 'นายเกรียงไกร', 'พลายงาม', 'บิดา', '5030126', '1909800301267'],
            ['6930127', 'ด.ญ.ทักษพร', 'ลิขิตตระกูล', 'หญิง', 85, 'ปกติ', 'นางนภา', 'ลิขิตตระกูล', 'มารดา', '5030127', '1909800301278'],
            ['6930128', 'ด.ญ.ธนัชชา', 'บุญพา', 'หญิง', 100, 'ปกติ', 'นายบุญส่ง', 'บุญพา', 'บิดา', '5030128', '1909800301289'],
            ['6930129', 'ด.ญ.นภัสสร', 'อัครเดชา', 'หญิง', 100, 'ปกติ', 'นางอรัญญา', 'อัครเดชา', 'มารดา', '5030129', '1909800301290'],
            ['6930130', 'ด.ญ.เบญญาภา', 'ฤทธิ์เดช', 'หญิง', 95, 'ปกติ', 'นายสุนทร', 'ฤทธิ์เดช', 'บิดา', '5030130', '1909800301301'],
            ['6930131', 'ด.ญ.ปภาวดี', 'รัตนโชติ', 'หญิง', 100, 'ปกติ', 'นางรัชนี', 'รัตนโชติ', 'มารดา', '5030131', '1909800301312'],
            ['6930132', 'ด.ญ.พิมพ์ชนก', 'แสงเพชร', 'หญิง', 75, 'เฝ้าระวัง', 'นายบุญเลิศ', 'แสงเพชร', 'บิดา', '5030132', '1909800301323'],
            ['6930133', 'ด.ญ.พิชญาภา', 'มั่นคงจิต', 'หญิง', 100, 'ปกติ', 'นางวันเพ็ญ', 'มั่นคงจิต', 'มารดา', '5030133', '1909800301334'],
            ['6930134', 'ด.ญ.มนัสวี', 'ศรีจันทร์', 'หญิง', 90, 'ปกติ', 'นายบุญมา', 'ศรีจันทร์', 'บิดา', '5030134', '1909800301345'],
            ['6930135', 'ด.ญ.รักษิณา', 'คงเจริญ', 'หญิง', 100, 'ปกติ', 'นางสุดา', 'คงเจริญ', 'มารดา', '5030135', '1909800301356'],
            ['6930136', 'ด.ญ.ลลิตา', 'อินทรสมบัติ', 'หญิง', 100, 'ปกติ', 'นายจำรัส', 'อินทรสมบัติ', 'บิดา', '5030136', '1909800301367'],
            ['6930137', 'ด.ญ.วรัทยา', 'เกียรติก้อง', 'หญิง', 95, 'ปกติ', 'นางมารศรี', 'เกียรติก้อง', 'มารดา', '5030137', '1909800301378'],
            ['6930138', 'ด.ญ.ศศิธร', 'บุญทศ', 'หญิง', 100, 'ปกติ', 'นายวิโรจน์', 'บุญทศ', 'บิดา', '5030138', '1909800301389'],
            ['6930139', 'ด.ญ.ศุภิสรา', 'รัตนโกสินทร์', 'หญิง', 100, 'ปกติ', 'นางสุนันทา', 'รัตนโกสินทร์', 'มารดา', '5030139', '1909800301390'],
            ['6930140', 'ด.ญ.อารียา', 'ปรีดารมย์', 'หญิง', 90, 'ปกติ', 'นายอำนวย', 'ปรีดารมย์', 'บิดา', '5030140', '1909800301401'],
        ];

        $createdCount = 0;

        foreach ($students as $st) {
            $studentId = $st[0];
            $firstName = $st[1];
            $lastName = $st[2];
            $gender = $st[3];
            $score = $st[4];
            $risk = $st[5];
            $parentFirst = $st[6];
            $parentLast = $st[7];
            $relation = $st[8];
            $parentUsername = $st[9];
            $citizenId = $st[10];

            // 1. Create or Find Student User
            $sUser = User::where('Username', $studentId)->first();
            if (!$sUser) {
                $sUser = User::create([
                    'UserID'    => (string) Str::uuid(),
                    'Username'  => $studentId,
                    'Password'  => Hash::make("Student{$studentId}"),
                    'CitizenID' => $citizenId,
                    'FirstName' => $firstName,
                    'LastName'  => $lastName,
                    'Role'      => 'นักเรียน',
                    'Email'     => "{$studentId}@student.yru.ac.th",
                    'Phone'     => '08' . rand(10000000, 99999999),
                    'Status'    => 'ปกติ',
                ]);
            } else {
                $sUser->update([
                    'Password' => Hash::make("Student{$studentId}"),
                ]);
            }

            // 2. Create or Find Parent User
            $pUser = User::where('Username', $parentUsername)->first();
            if (!$pUser) {
                $pUser = User::create([
                    'UserID'    => (string) Str::uuid(),
                    'Username'  => $parentUsername,
                    'Password'  => Hash::make("Parent{$parentUsername}"),
                    'CitizenID' => '3' . substr($citizenId, 1),
                    'FirstName' => $parentFirst,
                    'LastName'  => $parentLast,
                    'Role'      => 'ผู้ปกครอง',
                    'Email'     => "{$parentUsername}@parent.yru.ac.th",
                    'Phone'     => '08' . rand(10000000, 99999999),
                    'Status'    => 'ปกติ',
                ]);
            } else {
                $pUser->update([
                    'Password' => Hash::make("Parent{$parentUsername}"),
                ]);
            }

            // 3. Create or Find ParentGuardian record
            $parent = ParentGuardian::where('StudentID', $studentId)
                ->orWhere('UserID', $pUser->UserID)
                ->first();

            if (!$parent) {
                $parent = ParentGuardian::create([
                    'ParentID'     => (string) Str::uuid(),
                    'UserID'       => $pUser->UserID,
                    'StudentID'    => null,
                    'FirstName'    => $parentFirst,
                    'LastName'     => $parentLast,
                    'Relationship' => $relation,
                    'Phone'        => $pUser->Phone,
                    'Email'        => $pUser->Email,
                    'Address'      => '123/45 หมู่ ' . rand(1, 9) . ' ต.สะเตง อ.เมือง จ.ยะลา 95000',
                ]);
            }

            // 4. Create or Find Student Record
            $student = Student::where('StudentID', $studentId)->first();
            if (!$student) {
                $student = Student::create([
                    'StudentID'     => $studentId,
                    'UserID'        => $sUser->UserID,
                    'ParentID'      => $parent->ParentID,
                    'FirstName'     => $firstName,
                    'LastName'      => $lastName,
                    'GradeLevel'    => 'ม.3',
                    'Classroom'     => 'ม.3/1',
                    'Gender'        => $gender,
                    'BehaviorScore' => $score,
                    'RiskStatus'    => $risk,
                ]);
            } else {
                $student->update([
                    'ParentID'   => $parent->ParentID,
                    'GradeLevel' => 'ม.3',
                    'Classroom'  => 'ม.3/1',
                    'Gender'     => $gender,
                ]);
            }

            // 5. Connect Parent to StudentID
            $parent->update(['StudentID' => $student->StudentID]);

            // 6. Create corresponding Behavior Records if score < 100
            $deductNeeded = 100 - $score;
            if ($deductNeeded > 0) {
                $activeSemester = \App\Models\Semester::where('is_active', true)->first() 
                               ?? \App\Models\Semester::latest('id')->first();
                $semesterId = $activeSemester ? $activeSemester->semester_id : null;
                $recorder = User::where('Role', 'ฝ่ายปกครอง')->first() ?? User::where('Role', 'ครู')->first();

                // Check existing deduction sum
                $existingDeduct = \App\Models\BehaviorRecord::where('StudentID', $student->StudentID)
                    ->when($semesterId, fn($q) => $q->where('semester_id', $semesterId))
                    ->whereIn('Status', ['อนุมัติ', 'อนุมัติแล้ว', 'อยู่ในระหว่างยื่นอุทธรณ์'])
                    ->join('behavior_rules', 'behavior_records.RuleID', '=', 'behavior_rules.RuleID')
                    ->where('behavior_rules.RuleType', 'ตัดคะแนน')
                    ->sum(\Illuminate\Support\Facades\DB::raw('ABS(behavior_rules.ScoreModifier)'));

                $remainingToDeduct = $deductNeeded - $existingDeduct;

                if ($remainingToDeduct > 0) {
                    $rulesPool = \App\Models\BehaviorRule::where('RuleType', 'ตัดคะแนน')->get();

                    while ($remainingToDeduct > 0) {
                        // Find a rule that matches remaining deduction or <= remaining
                        $rule = $rulesPool->where('ScoreModifier', -$remainingToDeduct)->first();
                        if (!$rule) {
                            $rule = $rulesPool->filter(fn($r) => abs($r->ScoreModifier) <= $remainingToDeduct && abs($r->ScoreModifier) > 0)
                                              ->sortByDesc(fn($r) => abs($r->ScoreModifier))
                                              ->first();
                        }
                        if (!$rule) {
                            $rule = $rulesPool->first();
                        }

                        $deductAmount = abs($rule->ScoreModifier);
                        if ($deductAmount <= 0) break;

                        \App\Models\BehaviorRecord::create([
                            'RecordID'    => (string) Str::uuid(),
                            'StudentID'   => $student->StudentID,
                            'RecordedBy'  => $recorder ? $recorder->UserID : $sUser->UserID,
                            'RuleID'      => $rule->RuleID,
                            'Description' => 'บันทึกพฤติกรรม: ' . $rule->RuleName . ' (ห้อง 3/1)',
                            'RecordDate'  => \Carbon\Carbon::now()->subDays(rand(1, 45))->format('Y-m-d'),
                            'Penalty'     => $deductAmount >= 20 ? 'ว่ากล่าวตักเตือนและบำเพ็ญประโยชน์' : 'ว่ากล่าวตักเตือน',
                            'Status'      => 'อนุมัติแล้ว',
                            'semester_id' => $semesterId,
                        ]);

                        $remainingToDeduct -= $deductAmount;
                    }
                }
            }

            $createdCount++;
        }

        return $createdCount;
    }

    public function run(): void
    {
        self::seed40Students();
    }
}
