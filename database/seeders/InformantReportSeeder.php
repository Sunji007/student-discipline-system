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
                'Category'    => 'สิ่งเสพติดและอบายมุข',
                'desc'        => 'พบนักเรียนกลุ่มหนึ่งสูบบุหรี่บริเวณหลังโรงเรียน ช่วงพักกลางวัน ประมาณ 3–4 คน',
                'status'      => 'กำลังตรวจสอบ',
                'days'        => 3,
                'IsAnonymous' => true,
                'StudentID'   => '10004',
                'ReporterID'  => null,
                'ReporterName'=> null,
            ],
            [
                'Title'       => 'ทุจริตนำโทรศัพท์เข้าห้องสอบ',
                'Category'    => 'อื่นๆ',
                'desc'        => 'มีนักเรียนนำโทรศัพท์เข้าห้องสอบ สังเกตเห็นในช่วงสอบกลางภาค',
                'status'      => 'รอดำเนินการ',
                'days'        => 1,
                'IsAnonymous' => false,
                'StudentID'   => '10002',
                'ReporterID'  => $studentUser ? $studentUser->UserID : null,
                'ReporterName'=> $studentUser ? $studentUser->FullName : 'ด.ช. เก่ง เรียนดี',
            ],
            [
                'Title'       => 'นักเรียนลักลอบออกนอกโรงเรียน',
                'Category'    => 'หนีเรียน',
                'desc'        => 'นักเรียนชั้น ม.2 กลุ่มหนึ่งมักออกนอกโรงเรียนในช่วงพัก โดยไม่ได้รับอนุญาต',
                'status'      => 'ดำเนินการแล้ว',
                'days'        => 15,
                'IsAnonymous' => false,
                'StudentID'   => '10005',
                'ReporterID'  => $teacherUser ? $teacherUser->UserID : null,
                'ReporterName'=> $teacherUser ? $teacherUser->FullName : 'ครูสมหญิง ยอดรัก',
            ],
            [
                'Title'       => 'เหตุทะเลาะวิวาทบริเวณโรงอาหาร',
                'Category'    => 'ทะเลาะวิวาท',
                'desc'        => 'พบการทะเลาะวิวาทระหว่างนักเรียนต่างห้อง บริเวณโรงอาหาร',
                'status'      => 'กำลังตรวจสอบ',
                'days'        => 5,
                'IsAnonymous' => true,
                'StudentID'   => '10003',
                'ReporterID'  => null,
                'ReporterName'=> null,
            ],
            [
                'Title'       => 'พกพาสิ่งของมีคมเข้าโรงเรียน',
                'Category'    => 'อื่นๆ',
                'desc'        => 'นักเรียนนำสิ่งของมีคมเข้าโรงเรียน พบในกระเป๋านักเรียนชั้น ม.3',
                'status'      => 'รอดำเนินการ',
                'days'        => 2,
                'IsAnonymous' => true,
                'StudentID'   => '10006',
                'ReporterID'  => null,
                'ReporterName'=> null,
            ],
            [
                'Title'       => 'เหตุลักขโมยทรัพย์สินในห้องเรียน ม.1/1',
                'Category'    => 'อื่นๆ',
                'desc'        => 'มีการลักขโมยของในห้องเรียน ม.1/1 หลายครั้งในสัปดาห์นี้',
                'status'      => 'กำลังตรวจสอบ',
                'days'        => 4,
                'IsAnonymous' => false,
                'StudentID'   => '10001',
                'ReporterID'  => $studentUser ? $studentUser->UserID : null,
                'ReporterName'=> $studentUser ? $studentUser->FullName : 'ด.ช. เก่ง เรียนดี',
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