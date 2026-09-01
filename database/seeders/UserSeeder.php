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
            'UserID'    => Str::uuid(),
            'Username'  => '40001',
            'Password'  => Hash::make('Admin40001'),
            'FirstName' => 'นายสมชาย',
            'LastName'  => 'ใจดี',
            'Role'      => 'ผู้ดูแลระบบ',
            'Email'     => 'admin@example.com',
            'Phone'     => '0800000000',
            'Status'    => 'ปกติ',
        ]);

        // ===== ฝ่ายปกครอง =====
        $discipline1Id = Str::uuid();
        User::create([
            'UserID'    => $discipline1Id,
            'Username'  => '30001',
            'Password'  => Hash::make('Discipline30001'),
            'FirstName' => 'นายอับดุลเลาะ',
            'LastName'  => 'มะแอ',
            'Role'      => 'ฝ่ายปกครอง',
            'Email'     => 'discipline01@example.com',
            'Phone'     => '0876543210',
            'Status'    => 'ปกติ',
        ]);
        DisciplineStaff::create([
            'StaffID'  => Str::uuid(),
            'UserID'   => $discipline1Id,
            'Position' => 'หัวหน้าฝ่ายปกครอง',
            'Level'    => 'อนุมัติผล/ตั้งค่า',
        ]);

        $discipline2Id = Str::uuid();
        User::create([
            'UserID'    => $discipline2Id,
            'Username'  => '30002',
            'Password'  => Hash::make('Discipline30002'),
            'FirstName' => 'นางสาวฟาติมะห์',
            'LastName'  => 'ดอเลาะ',
            'Role'      => 'ฝ่ายปกครอง',
            'Email'     => 'discipline02@example.com',
            'Phone'     => '0876543211',
            'Status'    => 'ปกติ',
        ]);
        DisciplineStaff::create([
            'StaffID'  => Str::uuid(),
            'UserID'   => $discipline2Id,
            'Position' => 'เจ้าหน้าที่ฝ่ายปกครอง',
            'Level'    => 'บันทึกได้',
        ]);

        // ===== ครู =====
        $teacherData = [
            ['20001', 'นางสาวนูรีดา', 'สาและ',  'คณิตศาสตร์',  'ม.1/1'],
            ['20002', 'นายซูไฮมี', 'มะเซ็ง',    'วิทยาศาสตร์และเทคโนโลยี', 'ม.2/1'],
            ['20003', 'นางรอฮานี', 'ยามา',       'ภาษาไทย',     'ม.3/1'],
            ['20004', 'นายอาดิล', 'แวดอเลาะ',   'สังคมศึกษา ศาสนา และวัฒนธรรม',  'ม.4/1'],
            ['20005', 'นางสาวซาฟีนะห์', 'กาเซ็ง','ภาษาต่างประเทศ', 'ม.5/1'],
        ];

        foreach ($teacherData as [$username, $firstname, $lastname, $deptName, $room]) {
            $uid = Str::uuid();
            User::create([
                'UserID'    => $uid,
                'Username'  => $username,
                'Password'  => Hash::make("Teacher" . $username),
                'FirstName' => $firstname,
                'LastName'  => $lastname,
                'Role'      => 'ครู',
                'Email'     => $username . '@example.com',
                'Phone'     => '0898765432',
                'Status'    => 'ปกติ',
            ]);

            $dept = \App\Models\Department::where('short_name', $deptName)
                ->orWhere('name', $deptName)
                ->first();

            $teacher = Teacher::create([
                'TeacherID'     => $username,
                'UserID'        => $uid,
                'department_id' => $dept?->department_id,
            ]);

            DB::table('teacher_advisory_rooms')->insert([
                'TeacherID'  => $username,
                'Classroom'  => preg_replace('/^ม\./', '', $room),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}