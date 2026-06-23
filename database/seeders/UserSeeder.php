<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Teacher;
use App\Models\DisciplineStaff;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ===== ผู้ดูแลระบบ =====
        User::create([
            'UserID'   => Str::uuid(),
            'Username' => 'admin',
            'Password' => Hash::make('admin1234'),
            'FullName' => 'นายสมชาย ใจดี',
            'Role'     => 'ผู้ดูแลระบบ',
            'Status'   => 'ปกติ',
        ]);

        // ===== ฝ่ายปกครอง =====
        $discipline1Id = Str::uuid();
        User::create([
            'UserID'   => $discipline1Id,
            'Username' => 'discipline01',
            'Password' => Hash::make('pass1234'),
            'FullName' => 'นายอับดุลเลาะ มะแอ',
            'Role'     => 'ฝ่ายปกครอง',
            'Status'   => 'ปกติ',
        ]);
        DisciplineStaff::create([
            'StaffID'  => Str::uuid(),
            'UserID'   => $discipline1Id,
            'Position' => 'หัวหน้าฝ่ายปกครอง',
            'Level'    => 'อนุมัติผล/ตั้งค่า',
        ]);

        $discipline2Id = Str::uuid();
        User::create([
            'UserID'   => $discipline2Id,
            'Username' => 'discipline02',
            'Password' => Hash::make('pass1234'),
            'FullName' => 'นางสาวฟาติมะห์ ดอเลาะ',
            'Role'     => 'ฝ่ายปกครอง',
            'Status'   => 'ปกติ',
        ]);
        DisciplineStaff::create([
            'StaffID'  => Str::uuid(),
            'UserID'   => $discipline2Id,
            'Position' => 'เจ้าหน้าที่ฝ่ายปกครอง',
            'Level'    => 'บันทึกได้',
        ]);

        // ===== ครู =====
        $teacherData = [
            ['teacher01', 'นางสาวนูรีดา สาและ',  'คณิตศาสตร์',  'ม.1/1'],
            ['teacher02', 'นายซูไฮมี มะเซ็ง',    'วิทยาศาสตร์', 'ม.2/1'],
            ['teacher03', 'นางรอฮานี ยามา',       'ภาษาไทย',     'ม.3/1'],
            ['teacher04', 'นายอาดิล แวดอเลาะ',   'สังคมศึกษา',  'ม.4/1'],
            ['teacher05', 'นางสาวซาฟีนะห์ กาเซ็ง','ภาษาอังกฤษ', 'ม.5/1'],
        ];

        foreach ($teacherData as [$username, $fullname, $dept, $room]) {
            $uid = Str::uuid();
            User::create([
                'UserID'   => $uid,
                'Username' => $username,
                'Password' => Hash::make('pass1234'),
                'FullName' => $fullname,
                'Role'     => 'ครู',
                'Status'   => 'ปกติ',
            ]);
            Teacher::create([
                'TeacherID'    => $username,
                'UserID'       => $uid,
                'Department'   => $dept,
                'AdvisoryRoom' => $room,
            ]);
        }
    }
}