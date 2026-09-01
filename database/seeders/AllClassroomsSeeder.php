<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentGuardian;
use App\Models\PrayerRecord;
use App\Models\Semester;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AllClassroomsSeeder extends Seeder
{
    public function run(): void
    {
        $activeSemester = Semester::where('is_active', true)->first() ?? Semester::first();
        $semesterId = $activeSemester ? $activeSemester->semester_id : null;

        // 1. Clean up / Delete all students and related data in rooms /5 and /6
        $removedStudents = Student::where('Classroom', 'like', '%/5')
            ->orWhere('Classroom', 'like', '%/6')
            ->get();

        foreach ($removedStudents as $st) {
            $studentId = $st->StudentID;
            $userId = $st->UserID;
            $parentId = $st->ParentID;

            PrayerRecord::where('StudentID', $studentId)->delete();
            DB::table('behavior_records')->where('StudentID', $studentId)->delete();
            DB::table('attendances')->where('StudentID', $studentId)->delete();

            if ($parentId) {
                $parent = ParentGuardian::find($parentId);
                if ($parent) {
                    $pUserId = $parent->UserID;
                    $parent->delete();
                    if ($pUserId) User::where('UserID', $pUserId)->delete();
                }
            }

            $st->delete();
            if ($userId) User::where('UserID', $userId)->delete();
        }

        $maleFirstNames = [
            'กิตติพงษ์', 'ธนกฤต', 'ภานุวัฒน์', 'วรภพ', 'ณัฐดนัย', 'ชลธี', 'ธีรภัทร', 
            'ปริญญา', 'ศุภกร', 'ภาสกร', 'ธนภัทร', 'นวพล', 'ปิยวัฒน์', 'วุฒิชัย', 
            'ชินดนัย', 'ภูวดล', 'อภิสิทธิ์', 'วรเมธ', 'มะซู', 'มูฮัมหมัด', 'อิลฮัม', 
            'ฟาอิส', 'อับดุลเลาะห์', 'รุสลัน', 'อิสมาแอล', 'ฮาซัน', 'อานัส', 'ซูไฮมี',
            'อาฟันดี', 'ริดวาน', 'อันวาร์', 'ลุกมาน', 'ฮัมดี', 'ฟาริด', 'ซุลกิฟลี',
            'เอกชัย', 'ชัยวัฒน์', 'พัฒนพงษ์', 'พงศกร', 'กฤษณะ', 'ทรงพล', 'วัชระ',
            'สิรภพ', 'พีรพล', 'เกียรติศักดิ์', 'นพรัตน์', 'อัครเดช', 'จิรภัทร', 'ปิยะณัฐ'
        ];

        $femaleFirstNames = [
            'นูรฮายาตี', 'ฟาตีมะห์', 'อามีนะห์', 'นัสรีน', 'นูรีซัน', 'มัรยัม', 'กัญญาภัทร', 
            'ชนนิกานต์', 'ณิชากร', 'ธัญชนก', 'ปวริศา', 'ภัทรวดี', 'ศศิธร', 'อมรรัตน์', 
            'วิภาดา', 'สุดารัตน์', 'ชนิดาภา', 'นภัสสร', 'กนกวรรณ', 'อังคณา', 'ซาฟีนะห์', 
            'นูรีดา', 'ฮานาน', 'ยาซีเราะห์', 'ตัสนีม', 'ซอฟียะห์', 'รอยฮานา', 'อานีซา',
            'สุไรดา', 'ฟาดีละห์', 'คอดียะห์', 'ไซหนับ', 'ซาฮีดะห์', 'มัสตูเราะห์', 'นาดิยา',
            'กัญญารัตน์', 'จิราภรณ์', 'ทิพวรรณ', 'เบญจมาศ', 'พรทิพย์', 'วรรณภา', 'ศิริพร',
            'สุพรรษา', 'อารียา', 'พัชราภา', 'พิมพ์ชนก', 'มนัสนันท์', 'รุ่งทิวา', 'ลลิตา'
        ];

        $lastNames = [
            'สุวรรณรัตน์', 'แก้วมณี', 'คงเจริญ', 'ศรีสุข', 'แสงสว่าง', 'รักษาวงศ์', 'ชูชาติ', 
            'วงศ์สวัสดิ์', 'เมฆา', 'ว่องไว', 'ใจมั่น', 'รัตนกุล', 'บุญเกิด', 'ศรีมงคล', 
            'ทองดี', 'พรหมรักษา', 'อินทร์ทอง', 'พงษ์สวัสดิ์', 'มะยูโซ๊ะ', 'ดอเลาะ', 'สาและ', 
            'ยามา', 'สุหลง', 'วาเด็ง', 'ดือราแม', 'สาแม', 'เจ๊ะอาลี', 'สะดียามู', 
            'หะยีบากา', 'หะยีอาแว', 'มะดากะกุล', 'ศรีสวัสดิ์', 'เกตุแก้ว', 'มณีรัตน์', 
            'บุญมี', 'รักษ์ดี', 'สุขสมบัติ', 'พานิช', 'ทองหล่อ', 'เจริญสุข', 'เพชรศรี',
            'บือราเฮง', 'มะแซ', 'เจ๊ะมะ', 'แวซู', 'ดอเล๊าะ', 'กะดะโมะ', 'หะยีดาโอ๊ะ',
            'เด่นดวง', 'เจริญรัตน์', 'ทรัพย์สมบูรณ์', 'วัฒนพาณิชย์', 'มหาโชค', 'ศิริวัฒน์'
        ];

        $parentFirstNames = [
            'สมคิด', 'กัลยา', 'ขจร', 'ดนัย', 'สมศรี', 'ประสงค์', 'สืบ', 'วิเชียร', 
            'มานะ', 'วันชัย', 'อำนาจ', 'ประสิทธิ์', 'สมศักดิ์', 'มาลี', 'วรรณา', 
            'อับดุลรอมาน', 'มะรูดิง', 'มาหามะ', 'เจ๊ะดอเลาะ', 'ซาการียา', 'อิบรอฮีม',
            'มัสลัน', 'ยูโซ๊ะ', 'อาหะมะ', 'สาเหาะ', 'แวอูเซ็ง', 'รอปิ', 'ดอแม'
        ];

        // Sample check-in dates for August (Month 8) AND September (Month 9)
        $datesForPrayer = [
            // August
            '2026-08-03', '2026-08-05', '2026-08-07', '2026-08-10', '2026-08-14',
            '2026-08-18', '2026-08-20', '2026-08-24', '2026-08-27', '2026-08-31',
            // September (Today & early September)
            '2026-09-01'
        ];

        $disciplineUser = User::where('Role', 'ฝ่ายปกครอง')->first() ?? User::first();
        $recorderId = $disciplineUser ? $disciplineUser->UserID : null;

        $prayerBatch = [];
        $nowStr = Carbon::now()->toDateTimeString();

        // 2. Loop through Grades ม.1 to ม.6, Rooms 1 to 4 (Total 24 Classrooms, 20 students each)
        for ($gradeNum = 1; $gradeNum <= 6; $gradeNum++) {
            $gradeLevel = "ม.{$gradeNum}";

            for ($roomNum = 1; $roomNum <= 4; $roomNum++) {
                $classroom = "ม.{$gradeNum}/{$roomNum}";

                for ($s = 1; $s <= 20; $s++) {
                    $studentId = sprintf("69%d%02d%02d", $gradeNum, $roomNum, $s);
                    $parentUsername = sprintf("50%d%02d%02d", $gradeNum, $roomNum, $s);

                    $isMale = ($s % 2 === 1);
                    $gender = $isMale ? 'ชาย' : 'หญิง';

                    $firstName = $isMale 
                        ? $maleFirstNames[($gradeNum * 7 + $roomNum * 5 + $s) % count($maleFirstNames)] 
                        : $femaleFirstNames[($gradeNum * 7 + $roomNum * 5 + $s) % count($femaleFirstNames)];
                    
                    $prefix = ($gradeNum <= 3) 
                        ? ($isMale ? 'ด.ช.' : 'ด.ญ.') 
                        : ($isMale ? 'นาย' : 'นางสาว');
                    
                    $fullName = $prefix . ' ' . $firstName;
                    $lastName = $lastNames[($gradeNum * 11 + $roomNum * 7 + $s * 3) % count($lastNames)];

                    // Realistic Behavior Score
                    $scoreSeed = rand(1, 100);
                    if ($scoreSeed > 25) {
                        $score = rand(85, 100);
                        $risk = 'ปกติ';
                    } elseif ($scoreSeed > 8) {
                        $score = rand(65, 79);
                        $risk = 'ตักเตือน';
                    } else {
                        $score = rand(45, 59);
                        $risk = 'ทัณฑ์บน';
                    }

                    // Check or create parent user
                    $pUser = User::where('Username', $parentUsername)->first();
                    if (!$pUser) {
                        $parentUuid = (string) Str::uuid();
                        $parentFirst = $parentFirstNames[($gradeNum * 5 + $s) % count($parentFirstNames)];
                        $relation = ($s % 2 === 1) ? 'บิดา' : 'มารดา';

                        $pUser = User::create([
                            'UserID'    => $parentUuid,
                            'Username'  => $parentUsername,
                            'Password'  => Hash::make("Parent{$parentUsername}"),
                            'FirstName' => $relation === 'บิดา' ? 'นาย' . $parentFirst : 'นาง' . $parentFirst,
                            'LastName'  => $lastName,
                            'Role'      => 'ผู้ปกครอง',
                            'Email'     => "parent_{$studentId}@yru.ac.th",
                            'Phone'     => '08' . rand(10000000, 99999999),
                            'Status'    => 'ปกติ',
                        ]);
                    }

                    // Check or create parent guardian
                    $parent = ParentGuardian::where('UserID', $pUser->UserID)->first();
                    if (!$parent) {
                        $parentFirst = $parentFirstNames[($gradeNum * 5 + $s) % count($parentFirstNames)];
                        $relation = ($s % 2 === 1) ? 'บิดา' : 'มารดา';

                        $parent = ParentGuardian::create([
                            'ParentID'     => $pUser->UserID,
                            'UserID'       => $pUser->UserID,
                            'StudentID'    => null,
                            'FirstName'    => $relation === 'บิดา' ? 'นาย' . $parentFirst : 'นาง' . $parentFirst,
                            'LastName'     => $lastName,
                            'Relationship' => $relation,
                            'Phone'        => $pUser->Phone,
                            'Email'        => $pUser->Email,
                            'Address'      => 'หมู่บ้านราษฎร์พัฒนา ต.สะเตง อ.เมือง จ.ยะลา',
                        ]);
                    }

                    // Check or create student user
                    $sUser = User::where('Username', $studentId)->first();
                    if (!$sUser) {
                        $studentUuid = (string) Str::uuid();
                        $sUser = User::create([
                            'UserID'    => $studentUuid,
                            'Username'  => $studentId,
                            'Password'  => Hash::make("Student{$studentId}"),
                            'FirstName' => $fullName,
                            'LastName'  => $lastName,
                            'Role'      => 'นักเรียน',
                            'Email'     => "student_{$studentId}@yru.ac.th",
                            'Phone'     => '08' . rand(10000000, 99999999),
                            'Status'    => 'ปกติ',
                        ]);
                    }

                    // Check or create student
                    $student = Student::where('StudentID', $studentId)->first();
                    if (!$student) {
                        $student = Student::create([
                            'StudentID'     => $studentId,
                            'UserID'        => $sUser->UserID,
                            'ParentID'      => $parent->ParentID,
                            'FirstName'     => $fullName,
                            'LastName'      => $lastName,
                            'GradeLevel'    => $gradeLevel,
                            'Classroom'     => $classroom,
                            'Gender'        => $gender,
                            'BehaviorScore' => $score,
                            'RiskStatus'    => $risk,
                        ]);
                    } else {
                        $student->update([
                            'GradeLevel' => $gradeLevel,
                            'Classroom'  => $classroom,
                            'Gender'     => $gender,
                        ]);
                    }

                    $parent->update(['StudentID' => $student->StudentID]);

                    // Generate Prayer records for all sample dates if missing
                    $existingDates = PrayerRecord::where('StudentID', $studentId)->pluck('RecordDate')->toArray();
                    $attendanceRate = rand(60, 100);
                    $hasPeriodExempt = (!$isMale && rand(1, 10) <= 3);

                    foreach ($datesForPrayer as $dIndex => $dateStr) {
                        if (in_array($dateStr, $existingDates)) {
                            continue;
                        }

                        foreach (['ซุฮรี', 'อัศรี'] as $period) {
                            $isExemptDay = ($hasPeriodExempt && ($dIndex % 5 === 0 || $dIndex % 5 === 1));

                            if ($isExemptDay) {
                                $pStatus = 'ละหมาดไม่ได้';
                            } else {
                                $roll = rand(1, 100);
                                if ($roll <= $attendanceRate) {
                                    $pStatus = 'มา';
                                } else {
                                    $pStatus = 'ขาด';
                                }
                            }

                            $prayerBatch[] = [
                                'PrayerRecordID' => (string) Str::uuid(),
                                'StudentID'      => $studentId,
                                'RecordDate'     => $dateStr,
                                'RecordTime'     => ($period === 'ซุฮรี') ? '12:30:00' : '15:45:00',
                                'Period'         => $period,
                                'Status'         => $pStatus,
                                'RecordedBy'     => $recorderId,
                                'semester_id'    => $semesterId,
                                'created_at'     => $nowStr,
                                'updated_at'     => $nowStr,
                            ];
                        }
                    }
                }
            }
        }

        // Bulk insert prayer records in chunks of 500
        if (!empty($prayerBatch)) {
            foreach (array_chunk($prayerBatch, 500) as $chunk) {
                DB::table('prayer_records')->insert($chunk);
            }
        }
    }
}
