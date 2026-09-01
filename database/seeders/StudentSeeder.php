<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\ParentGuardian;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $studentsData = [
            ['6910101', '6910101', 'ด.ช. เก่ง', 'เรียนดี', 'ม.1', 'ม.1/1', '50001', 'นายสมคิด', 'เรียนดี', 'พ่อ', 100, 'ปกติ', 'stud-uuid-1', 'parent-uuid-1'],
            ['6910102', '6910102', 'ด.ญ. กานดา', 'รักดี', 'ม.1', 'ม.1/1', '50002', 'นางกัลยา', 'รักดี', 'แม่', 95, 'ปกติ', 'stud-uuid-2', 'parent-uuid-2'],
            ['6910201', '6910201', 'ด.ช. ขยัน', 'พากเพียร', 'ม.1', 'ม.1/2', '50003', 'นายขจร', 'พากเพียร', 'พ่อ', 85, 'ปกติ', 'stud-uuid-3', 'parent-uuid-3'],
            ['6910103', '6910103', 'ด.ช. เด็กดื้อ', 'ดนตรี', 'ม.1', 'ม.1/1', '50004', 'นายดนัย', 'ดนตรี', 'พ่อ', 55, 'วิกฤต', 'stud-uuid-4', 'parent-uuid-4'],
            ['6920101', '6920101', 'ด.ญ. สมใจ', 'ยินดี', 'ม.2', 'ม.2/1', '50005', 'นางสมศรี', 'ยินดี', 'แม่', 75, 'เฝ้าระวัง', 'stud-uuid-5', 'parent-uuid-5'],
            ['6930101', '6930101', 'ด.ช. ปัญญา', 'เลิศล้ำ', 'ม.3', 'ม.3/1', '50006', 'นายประสงค์', 'เลิศล้ำ', 'พ่อ', 100, 'ปกติ', 'stud-uuid-6', 'parent-uuid-6'],
            ['6940201', '6940201', 'นางสาวอิมรอน', 'สืบแม', 'ม.4', 'ม.4/2', '50007', 'นายสืบ', 'สืบแม', 'พ่อ', 90, 'ปกติ', 'stud-uuid-7', 'parent-uuid-7'],
        ];

        foreach ($studentsData as $st) {
            $studentId = $st[1];
            $parentUsername = $st[6];
            $parentPassword = Hash::make("Parent{$parentUsername}");
            $studentPassword = Hash::make("Student{$studentId}");

            // Create Parent User & Parent Model
            $pUser = User::create([
                'UserID'    => $st[13],
                'Username'  => $parentUsername,
                'Password'  => $parentPassword,
                'FirstName' => $st[7],
                'LastName'  => $st[8],
                'Role'      => 'ผู้ปกครอง',
                'Email'     => $parentUsername . '@example.com',
                'Phone'     => '0812345678',
                'Status'    => 'ปกติ',
            ]);
            $parent = ParentGuardian::create([
                'ParentID'     => $st[13], // Use parent UserID as ParentID
                'UserID'       => $pUser->UserID,
                'FirstName'    => $st[7],
                'LastName'     => $st[8],
                'Relationship' => $st[9],
                'Phone'        => '0812345678',
                'Email'        => $parentUsername . '@example.com',
                'Address'      => '123/45 ถนนราษฎร์บำรุง อำเภอเมือง จังหวัดเชียงใหม่',
            ]);

            // Create Student User & Student Model
            $sUser = User::create([
                'UserID'    => $st[12],
                'Username'  => $st[0],
                'Password'  => $studentPassword,
                'FirstName' => $st[2],
                'LastName'  => $st[3],
                'Role'      => 'นักเรียน',
                'Email'     => $st[0] . '@example.com',
                'Phone'     => '0823456789',
                'Status'    => 'ปกติ',
            ]);
            $student = Student::create([
                'StudentID'     => $st[1],
                'UserID'        => $sUser->UserID,
                'ParentID'      => $parent->ParentID,
                'FirstName'     => $st[2],
                'LastName'      => $st[3],
                'GradeLevel'    => $st[4],
                'Classroom'     => $st[5],
                'BehaviorScore' => $st[10],
                'RiskStatus'    => $st[11],
                'Photo'         => 'photos/' . $st[1] . '.png',
            ]);

            // Update parent relationship
            $parent->update(['StudentID' => $student->StudentID]);
        }
    }
}
