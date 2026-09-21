<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentGuardian;
use App\Models\DisciplineStaff;
use App\Models\TeacherAdvisoryRoom;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password123');

        // 1. Admin (Username: admin, Password: password123)
        $admin = User::where('Username', 'admin')->first();
        if (!$admin) {
            User::create([
                'UserID'    => (string) Str::uuid(),
                'Username'  => 'admin',
                'FirstName' => 'ผู้ดูแลระบบ',
                'LastName'  => 'Admin System',
                'Role'      => 'ผู้ดูแลระบบ',
                'Email'     => 'admin@example.com',
                'Phone'     => '0800000000',
                'Password'  => $password,
                'Status'    => 'ปกติ',
            ]);
        } else {
            $admin->update(['Password' => $password, 'Role' => 'ผู้ดูแลระบบ']);
        }

        // 2. Discipline (Username: discipline1, Password: password123)
        $disciplineUser = User::where('Username', 'discipline1')->first();
        if (!$disciplineUser) {
            $disciplineUser = User::create([
                'UserID'    => (string) Str::uuid(),
                'Username'  => 'discipline1',
                'FirstName' => 'ครูสมชาย',
                'LastName'  => 'ฝ่ายปกครอง',
                'Role'      => 'ฝ่ายปกครอง',
                'Email'     => 'discipline1@example.com',
                'Phone'     => '0811111111',
                'Password'  => $password,
                'Status'    => 'ปกติ',
            ]);
            DisciplineStaff::create([
                'StaffID'  => (string) Str::uuid(),
                'UserID'   => $disciplineUser->UserID,
                'Position' => 'หัวหน้าฝ่ายปกครอง',
                'Level'    => 'อนุมัติผล/ตั้งค่า',
            ]);
        } else {
            $disciplineUser->update(['Password' => $password, 'Role' => 'ฝ่ายปกครอง']);
        }

        // 3. Teacher (Username: teacher1, Password: password123)
        $teacherUser = User::where('Username', 'teacher1')->first();
        if (!$teacherUser) {
            $teacherUser = User::create([
                'UserID'    => (string) Str::uuid(),
                'Username'  => 'teacher1',
                'FirstName' => 'ครูสมหญิง',
                'LastName'  => 'ที่ปรึกษา',
                'Role'      => 'ครู',
                'Email'     => 'teacher1@example.com',
                'Phone'     => '0822222222',
                'Password'  => $password,
                'Status'    => 'ปกติ',
            ]);
            Teacher::create([
                'TeacherID' => 'teacher1',
                'UserID'    => $teacherUser->UserID,
            ]);
            TeacherAdvisoryRoom::create([
                'TeacherID' => 'teacher1',
                'Classroom' => '1/1'
            ]);
        } else {
            $teacherUser->update(['Password' => $password, 'Role' => 'ครู']);
        }

        // 4. Student (Username: student1, Password: password123)
        $studentUser = User::where('Username', 'student1')->first();
        if (!$studentUser) {
            $studentUser = User::create([
                'UserID'    => (string) Str::uuid(),
                'Username'  => 'student1',
                'FirstName' => 'ด.ช. ทดสอบ',
                'LastName'  => 'ระบบ',
                'Role'      => 'นักเรียน',
                'Email'     => 'student1@example.com',
                'Phone'     => '0844444444',
                'Password'  => $password,
                'Status'    => 'ปกติ',
            ]);
            $student = Student::create([
                'StudentID'     => 'student1',
                'UserID'        => $studentUser->UserID,
                'FirstName'     => 'ด.ช. ทดสอบ',
                'LastName'      => 'ระบบ',
                'GradeLevel'    => 'ม.1',
                'Classroom'     => '1/1',
                'BehaviorScore' => 100,
                'RiskStatus'    => 'ปกติ',
                'Gender'        => 'ชาย',
            ]);
        } else {
            $studentUser->update(['Password' => $password, 'Role' => 'นักเรียน']);
            $student = Student::where('StudentID', 'student1')->first();
        }

        // 5. Parent (Username: parent1, Password: password123)
        $parentUser = User::where('Username', 'parent1')->first();
        if (!$parentUser) {
            $parentUser = User::create([
                'UserID'    => (string) Str::uuid(),
                'Username'  => 'parent1',
                'FirstName' => 'ผู้ปกครอง',
                'LastName'  => 'ทดสอบ',
                'Role'      => 'ผู้ปกครอง',
                'Email'     => 'parent1@example.com',
                'Phone'     => '0833333333',
                'Password'  => $password,
                'Status'    => 'ปกติ',
            ]);
            $parent = ParentGuardian::create([
                'ParentID'     => (string) Str::uuid(),
                'UserID'       => $parentUser->UserID,
                'FirstName'    => 'ผู้ปกครอง',
                'LastName'     => 'ทดสอบ',
                'Relationship' => 1,
                'Phone'        => '0833333333',
                'Email'        => 'parent1@example.com',
                'StudentID'    => $student?->StudentID,
            ]);
            if ($student && $parent) {
                $student->update(['ParentID' => $parent->ParentID]);
            }
        } else {
            $parentUser->update(['Password' => $password, 'Role' => 'ผู้ปกครอง']);
        }
    }
}
