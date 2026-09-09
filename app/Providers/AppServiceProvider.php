<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ใช้ Bootstrap 5 สำหรับ Pagination
        Paginator::useBootstrap();

        if (request()->server('HTTP_HOST') && str_contains(request()->server('HTTP_HOST'), 'student.yru.ac.th')) {
            URL::forceScheme('https');
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        } elseif (request()->isSecure()) {
            URL::forceScheme('https');
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('informant_reports')) {
            if (!\Illuminate\Support\Facades\Schema::hasColumn('informant_reports', 'semester_id')) {
                try {
                    \Illuminate\Support\Facades\Schema::table('informant_reports', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->unsignedBigInteger('semester_id')->nullable()->after('ReportDate');
                    });
                } catch (\Throwable $e) {
                    // Ignore
                }
            }
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE informant_reports DROP FOREIGN KEY informant_reports_studentid_foreign");
            } catch (\Throwable $e) {}
            try {
                \Illuminate\Support\Facades\DB::statement("ALTER TABLE informant_reports MODIFY StudentID TEXT NULL");
            } catch (\Throwable $e) {}
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('behavior_rules')) {
            try {
                // If there are no positive rules, seed them once with permanent UUIDs
                if (\App\Models\BehaviorRule::where('RuleType', 'เพิ่มคะแนน')->count() === 0) {
                    $posRules = [
                        // 1. ความดีและจิตอาสา
                        ['เพิ่มคะแนน', 'ความดีและจิตอาสา', 'ช่วยเหลืองานโรงเรียน/กิจกรรมส่วนรวม', 5],
                        ['เพิ่มคะแนน', 'ความดีและจิตอาสา', 'ช่วยเหลือครูอาจารย์และบุคลากร', 5],
                        ['เพิ่มคะแนน', 'ความดีและจิตอาสา', 'จิตอาสาพัฒนาโรงเรียน/ทำความสะอาดพื้นที่ส่วนรวม', 5],
                        ['เพิ่มคะแนน', 'ความดีและจิตอาสา', 'ช่วยเหลือเพื่อนนักเรียน/ผู้สูงอายุ/ผู้พิการ', 5],
                        ['เพิ่มคะแนน', 'ความดีและจิตอาสา', 'เก็บสิ่งของหรือของมีค่าได้แล้วนำส่งคืนเจ้าของ', 10],
                        ['เพิ่มคะแนน', 'ความดีและจิตอาสา', 'บริจาคโลหิต/ทำกิจกรรมบำเพ็ญประโยชน์ต่อสังคม', 10],

                        // 2. กิจกรรมและสร้างชื่อเสียง
                        ['เพิ่มคะแนน', 'กิจกรรมและสร้างชื่อเสียง', 'เข้าร่วมกิจกรรมพิเศษของโรงเรียนหรือชุมชน', 5],
                        ['เพิ่มคะแนน', 'กิจกรรมและสร้างชื่อเสียง', 'เป็นตัวแทนโรงเรียนเข้าร่วมการแข่งขันระดับเขต/จังหวัด', 10],
                        ['เพิ่มคะแนน', 'กิจกรรมและสร้างชื่อเสียง', 'ได้รับรางวัลชนะเลิศ/รองชนะเลิศ ระดับจังหวัด/ภูมิภาค', 15],
                        ['เพิ่มคะแนน', 'กิจกรรมและสร้างชื่อเสียง', 'ได้รับรางวัลการแข่งขันระดับชาติ/นานาชาติ', 20],
                        ['เพิ่มคะแนน', 'กิจกรรมและสร้างชื่อเสียง', 'เข้าร่วมกิจกรรมค่ายวิชาการ/อบรมคุณธรรมจริยธรรม', 5],
                        ['เพิ่มคะแนน', 'กิจกรรมและสร้างชื่อเสียง', 'เข้าร่วมการแสดง/กิจกรรมเผยแพร่ชื่อเสียงโรงเรียน', 5],

                        // 3. ความประพฤติดีเด่นและวินัย
                        ['เพิ่มคะแนน', 'ความประพฤติดีเด่นและวินัย', 'ไม่เคยมาสายและเข้าแถวครบ 100% ตลอดเดือน', 5],
                        ['เพิ่มคะแนน', 'ความประพฤติดีเด่นและวินัย', 'ไม่มีบันทึกพฤติกรรมเชิงลบตลอดทั้งภาคเรียน', 10],
                        ['เพิ่มคะแนน', 'ความประพฤติดีเด่นและวินัย', 'ได้รับการคัดเลือกเป็นนักเรียนความประพฤติดีเด่นประจำเดือน', 10],
                        ['เพิ่มคะแนน', 'ความประพฤติดีเด่นและวินัย', 'ได้รับรางวัลนักเรียนดีเด่น/คนดีศรีโรงเรียน', 15],
                        ['เพิ่มคะแนน', 'ความประพฤติดีเด่นและวินัย', 'แต่งกายถูกต้องตามระเบียบเรียบร้อยเป็นแบบอย่างที่ดี', 5],

                        // 4. คุณธรรมและศาสนกิจ
                        ['เพิ่มคะแนน', 'คุณธรรมและศาสนกิจ', 'เข้าร่วมละหมาด/กิจกรรมศาสนกิจครบถ้วนสม่ำเสมอ', 5],
                        ['เพิ่มคะแนน', 'คุณธรรมและศาสนกิจ', 'เป็นผู้นำในการปฏิบัติศาสนกิจ/ผู้นำกิจกรรมทางศาสนา', 10],
                        ['เพิ่มคะแนน', 'คุณธรรมและศาสนกิจ', 'ปฏิบัติหน้าที่เวรศาสนกิจ/ดูแลสถานที่ประกอบศาสนกิจ', 5],
                        ['เพิ่มคะแนน', 'คุณธรรมและศาสนกิจ', 'สอบผ่านการทดสอบคุณธรรม/ศาสนศึกษาดีเด่น', 10],

                        // 5. ความเป็นผู้นำและการมีส่วนร่วม
                        ['เพิ่มคะแนน', 'ความเป็นผู้นำและการมีส่วนร่วม', 'ปฏิบัติหน้าที่คณะกรรมการสภานักเรียนด้วยความรับผิดชอบ', 10],
                        ['เพิ่มคะแนน', 'ความเป็นผู้นำและการมีส่วนร่วม', 'ปฏิบัติหน้าที่หัวหน้าห้อง/รองหัวหน้าห้องดีเด่น', 5],
                        ['เพิ่มคะแนน', 'ความเป็นผู้นำและการมีส่วนร่วม', 'เป็นพี่เลี้ยง/ผู้นำกิจกรรมดูแลน้องในกิจกรรมค่าย', 5],
                        ['เพิ่มคะแนน', 'ความเป็นผู้นำและการมีส่วนร่วม', 'ช่วยดูแลความสงบเรียบร้อยและความปลอดภัย (สารวัตรนักเรียน)', 10],
                        ['เพิ่มคะแนน', 'ความเป็นผู้นำและการมีส่วนร่วม', 'ได้รับคำชื่นชมเป็นลายลักษณ์อักษรจากหน่วยงานภายนอก/ชุมชน', 10],

                        // 6. วิชาการและความขยันหมั่นเพียร
                        ['เพิ่มคะแนน', 'วิชาการและความขยันหมั่นเพียร', 'มีผลการเรียนดีเยี่ยม (เกรดเฉลี่ย 3.50 ขึ้นไป)', 10],
                        ['เพิ่มคะแนน', 'วิชาการและความขยันหมั่นเพียร', 'มีพัฒนาการและผลการเรียนดีขึ้นอย่างเด่นชัด', 5],
                        ['เพิ่มคะแนน', 'วิชาการและความขยันหมั่นเพียร', 'จิตอาสาช่วยสอนการบ้าน/ติวหนังสือให้เพื่อนร่วมชั้น', 5],
                        ['เพิ่มคะแนน', 'วิชาการและความขยันหมั่นเพียร', 'ส่งงานและภาระงานครบถ้วนตรงเวลาสม่ำเสมอตลอดเดือน', 5],
                    ];

                    foreach ($posRules as [$type, $category, $name, $modifier]) {
                        \App\Models\BehaviorRule::firstOrCreate(
                            ['RuleName' => $name],
                            [
                                'RuleID'        => (string) \Illuminate\Support\Str::uuid(),
                                'RuleType'      => $type,
                                'Category'      => $category,
                                'ScoreModifier' => $modifier,
                            ]
                        );
                    }
                }

                // Ensure auto deduction rule exists
                \App\Models\BehaviorRule::firstOrCreate(
                    ['RuleName' => \App\Services\AttendanceDeductionService::RULE_NAME],
                    [
                        'RuleID'        => (string) \Illuminate\Support\Str::uuid(),
                        'RuleType'      => 'ตัดคะแนน',
                        'Category'      => 'การเข้าเรียนและระเบียบสถานศึกษา',
                        'ScoreModifier' => -5,
                        'Description'   => 'ระบบตัดคะแนนอัตโนมัติเมื่อขาดเข้าแถวสะสมครบทุก 3 ครั้ง (มาสายสะสม 3 ครั้ง = ขาด 1 ครั้ง)',
                    ]
                );
            } catch (\Throwable $e) {
                // Ignore
            }
        }

        if (\Illuminate\Support\Facades\Schema::hasTable('students')) {
            try {
                if (\App\Models\Student::where('Classroom', 'like', '%3/1%')->orWhere('Classroom', '3/1')->count() < 40) {
                    \Database\Seeders\Classroom31Seeder::seed40Students();
                }

                // ปรับข้อมูลห้องเรียนในฐานข้อมูลให้ตรงกับระดับชั้น (แก้ไขปัญหา ม.2/1/1 ให้เป็น 2/1 เสมอ)
                \Illuminate\Support\Facades\DB::statement("
                    UPDATE students 
                    SET Classroom = CONCAT(REPLACE(GradeLevel, 'ม.', ''), '/', SUBSTRING_INDEX(Classroom, '/', -1))
                    WHERE Classroom LIKE '%/%' 
                      AND GradeLevel IS NOT NULL
                      AND Classroom NOT LIKE CONCAT('%', REPLACE(GradeLevel, 'ม.', ''), '/%')
                ");
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
}
