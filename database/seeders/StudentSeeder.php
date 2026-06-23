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
        $password = Hash::make('pass1234');

        $studentsData = [
            ['student01', '10001', 'ด.ช. เก่ง เรียนดี', 'ม.1', 'ม.1/1', 'parent01', 'นายสมคิด เรียนดี', 'พ่อ', 100, 'ปกติ', 'stud-uuid-1', 'parent-uuid-1'],
            ['student02', '10002', 'ด.ญ. กานดา รักดี', 'ม.1', 'ม.1/1', 'parent02', 'นางกัลยา รักดี', 'แม่', 95, 'ปกติ', 'stud-uuid-2', 'parent-uuid-2'],
            ['student03', '10003', 'ด.ช. ขยัน พากเพียร', 'ม.1', 'ม.1/2', 'parent03', 'นายขจร พากเพียร', 'พ่อ', 85, 'ปกติ', 'stud-uuid-3', 'parent-uuid-3'],
            ['student04', '10004', 'ด.ช. เด็กดื้อ ดนตรี', 'ม.1', 'ม.1/1', 'parent04', 'นายดนัย ดนตรี', 'พ่อ', 55, 'วิกฤต', 'stud-uuid-4', 'parent-uuid-4'],
            ['student05', '10005', 'ด.ญ. สมใจ ยินดี', 'ม.2', 'ม.2/1', 'parent05', 'นางสมศรี ยินดี', 'แม่', 75, 'เฝ้าระวัง', 'stud-uuid-5', 'parent-uuid-5'],
            ['student06', '10006', 'ด.ช. ปัญญา เลิศล้ำ', 'ม.3', 'ม.3/1', 'parent06', 'นายประสงค์ เลิศล้ำ', 'พ่อ', 100, 'ปกติ', 'stud-uuid-6', 'parent-uuid-6'],
        ];

        foreach ($studentsData as $st) {
            // Create Parent User & Parent Model
            $pUser = User::create([
                'UserID' => $st[11],
                'Username' => $st[5],
                'Password' => $password,
                'FullName' => $st[6],
                'Role' => 'ผู้ปกครอง',
                'Status' => 'ปกติ',
            ]);
            $parent = ParentGuardian::create([
                'ParentID' => $st[11], // Use parent UserID as ParentID
                'UserID' => $pUser->UserID,
                'FullName' => $st[6],
                'Relationship' => $st[7],
                'Phone' => '0812345678',
                'Email' => $st[5] . '@example.com',
                'Address' => '123/45 ถนนราษฎร์บำรุง อำเภอเมือง จังหวัดเชียงใหม่',
            ]);

            // Create Student User & Student Model
            $sUser = User::create([
                'UserID' => $st[10],
                'Username' => $st[0],
                'Password' => $password,
                'FullName' => $st[2],
                'Role' => 'นักเรียน',
                'Status' => 'ปกติ',
            ]);
            $student = Student::create([
                'StudentID' => $st[1],
                'UserID' => $sUser->UserID,
                'ParentID' => $parent->ParentID,
                'FullName' => $st[2],
                'GradeLevel' => $st[3],
                'Classroom' => $st[4],
                'BehaviorScore' => $st[8],
                'RiskStatus' => $st[9],
                'Photo' => 'photos/' . $st[1] . '.png',
            ]);

            // Update parent relationship
            $parent->update(['StudentID' => $student->StudentID]);
        }
    }
}
