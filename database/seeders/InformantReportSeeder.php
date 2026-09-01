<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\InformantReport;
use App\Models\User;
use Carbon\Carbon;

class InformantReportSeeder extends Seeder
{
    public function run(): void
    {
        $studentUser = User::where('Role', 'นักเรียน')->first();
        $teacherUser = User::where('Role', 'ครู')->first();

        $reports = [
            [
                'Title'       => 'พฤติกรรมสูบบุหรี่หลังโรงเรียน',
                'Category'    => 'สารเสพติดและของต้องห้าม',
                'desc'        => 'พบนักเรียนกลุ่มหนึ่งสูบบุหรี่บริเวณหลังโรงเรียน ช่วงพักกลางวัน ประมาณ 3–4 คน',
                'status'      => 'กำลังตรวจสอบ',
                'days'        => 3,
                'IsAnonymous' => true,
                'StudentID'   => '6910103',
                'ReporterID'  => $studentUser ? $studentUser->UserID : 'USR-003',
                'ReporterName'=> $studentUser ? $studentUser->FullName : 'ด.ช. กษิดิศ วงศ์สว่าง',
            ],
            [
                'Title'       => 'ทุจริตนำโทรศัพท์เข้าห้องสอบ',
                'Category'    => 'การใช้เครื่องมือสื่อสาร',
                'desc'        => 'มีนักเรียนนำโทรศัพท์เข้าห้องสอบ สังเกตเห็นในช่วงสอบกลางภาค',
                'status'      => 'ปิดเรื่องแล้ว',
                'days'        => 1,
                'IsAnonymous' => false,
                'StudentID'   => '6910102',
                'ReporterID'  => $studentUser ? $studentUser->UserID : 'USR-003',
                'ReporterName'=> $studentUser ? $studentUser->FullName : 'ด.ช. กษิดิศ วงศ์สว่าง',
            ],
            [
                'Title'       => 'นักเรียนลักลอบออกนอกโรงเรียน',
                'Category'    => 'การเข้าเรียนและระเบียบสถานศึกษา',
                'desc'        => 'นักเรียนชั้น ม.2 กลุ่มหนึ่งมักออกนอกโรงเรียนในช่วงพัก โดยไม่ได้รับอนุญาต',
                'status'      => 'ดำเนินการแล้ว',
                'days'        => 15,
                'IsAnonymous' => false,
                'StudentID'   => '6920101',
                'ReporterID'  => $teacherUser ? $teacherUser->UserID : null,
                'ReporterName'=> $teacherUser ? $teacherUser->FullName : 'ครูสมหญิง ยอดรัก',
            ],
            [
                'Title'       => 'เหตุทะเลาะวิวาทบริเวณโรงอาหาร',
                'Category'    => 'ความประพฤติและกริยามารยาท',
                'desc'        => 'พบการทะเลาะวิวาทระหว่างนักเรียนต่างห้อง บริเวณโรงอาหาร',
                'status'      => 'กำลังตรวจสอบ',
                'days'        => 5,
                'IsAnonymous' => true,
                'StudentID'   => '6910201',
                'ReporterID'  => null,
                'ReporterName'=> null,
            ],
            [
                'Title'       => 'พกพาสิ่งของมีคมเข้าโรงเรียน',
                'Category'    => 'อื่นๆ',
                'desc'        => 'นักเรียนนำสิ่งของมีคมเข้าโรงเรียน พบในกระเป๋านักเรียนชั้น ม.3',
                'status'      => 'ปิดเรื่องแล้ว',
                'days'        => 2,
                'IsAnonymous' => true,
                'StudentID'   => '6930101',
                'ReporterID'  => $studentUser ? $studentUser->UserID : 'USR-003',
                'ReporterName'=> $studentUser ? $studentUser->FullName : 'ด.ช. กษิดิศ วงศ์สว่าง',
            ],
            [
                'Title'       => 'เหตุลักขโมยทรัพย์สินในห้องเรียน ม.1/1',
                'Category'    => 'อื่นๆ',
                'desc'        => 'มีการลักขโมยของในห้องเรียน ม.1/1 หลายครั้งในสัปดาห์นี้',
                'status'      => 'กำลังตรวจสอบ',
                'days'        => 4,
                'IsAnonymous' => false,
                'StudentID'   => '6910101',
                'ReporterID'  => $studentUser ? $studentUser->UserID : 'USR-003',
                'ReporterName'=> $studentUser ? $studentUser->FullName : 'ด.ช. กษิดิศ วงศ์สว่าง',
            ],
        ];

        foreach ($reports as $r) {
            InformantReport::create([
                'Title'        => $r['Title'],
                'Category'     => $r['Category'],
                'Description'  => $r['desc'],
                'EvidencePath' => null,
                'IsAnonymous'  => $r['IsAnonymous'],
                'StudentID'    => $r['StudentID'],
                'ReporterID'   => $r['ReporterID'],
                'ReporterName' => $r['ReporterName'],
                'Status'       => $r['status'],
                'created_at'   => Carbon::now()->subDays($r['days']),
                'updated_at'   => Carbon::now()->subDays($r['days']),
            ]);
        }
    }
}