<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\ParentGuardian;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUserSeeder extends Seeder
{
    public function run()
    {
        $password = Hash::make('password123');

        // Admin
        User::updateOrCreate(['Username' => 'admin'], [
            'UserID' => Str::uuid()->toString(),
            'FullName' => 'Admin System',
            'Role' => 'admin',
            'Password' => $password,
            'Status' => 'ปกติ'
        ]);

        // Discipline
        User::updateOrCreate(['Username' => 'discipline1'], [
            'UserID' => Str::uuid()->toString(),
            'FullName' => 'ครูสมชาย ฝ่ายปกครอง',
            'Role' => 'discipline',
            'Password' => $password,
            'Status' => 'ปกติ'
        ]);

        // Teacher
        $teacherUser = User::updateOrCreate(['Username' => 'teacher1'], [
            'UserID' => Str::uuid()->toString(),
            'FullName' => 'ครูสมหญิง ที่ปรึกษา',
            'Role' => 'teacher',
            'Password' => $password,
            'Status' => 'ปกติ'
        ]);
        Teacher::updateOrCreate(['UserID' => $teacherUser->UserID], [
            'TeacherID' => 'teacher1',
            'Department' => 'คณิตศาสตร์',
            'AdvisoryRoom' => 'ม.1/1'
        ]);

        // Parent
        $parentUser = User::updateOrCreate(['Username' => 'parent1'], [
            'UserID' => Str::uuid()->toString(),
            'FullName' => 'ผู้ปกครอง ทดสอบ',
            'Role' => 'parent',
            'Password' => $password,
            'Status' => 'ปกติ'
        ]);
        $parent = ParentGuardian::updateOrCreate(['UserID' => $parentUser->UserID], [
            'ParentID' => Str::uuid()->toString(),
            'FullName' => 'ผู้ปกครอง ทดสอบ',
            'Relationship' => 'บิดา',
            'StudentID' => null // Will update after student
        ]);

        // Student
        $studentUser = User::updateOrCreate(['Username' => 'student1'], [
            'UserID' => Str::uuid()->toString(),
            'FullName' => 'ด.ช. ทดสอบ ระบบ',
            'Role' => 'student',
            'Password' => $password,
            'Status' => 'ปกติ'
        ]);
        $student = Student::updateOrCreate(['StudentID' => '10001'], [
            'UserID' => $studentUser->UserID,
            'FullName' => 'ด.ช. ทดสอบ ระบบ',
            'GradeLevel' => 'ม.1',
            'Classroom' => '1/1',
            'BehaviorScore' => 100,
            'RiskStatus' => 'ปกติ',
            'ParentID' => $parent->ParentID
        ]);

        // Update Parent with StudentID
        $parent->update(['StudentID' => $student->StudentID]);
    }
}
