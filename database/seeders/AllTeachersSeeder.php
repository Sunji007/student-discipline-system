<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Teacher;
use App\Models\TeacherAdvisoryRoom;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AllTeachersSeeder extends Seeder
{
    public function run(): void
    {
        $teacherConfigs = [
            // ม.1
            'ม.1/1' => ['id' => '20101', 'name' => 'นายสมศักดิ์', 'last' => 'แดงดี', 'phone' => '0812341011'],
            'ม.1/2' => ['id' => '20102', 'name' => 'นางสาวนูรียา', 'last' => 'มะยูโซ๊ะ', 'phone' => '0812341012'],
            'ม.1/3' => ['id' => '20103', 'name' => 'นายวิรัช', 'last' => 'พรหมวิเศษ', 'phone' => '0812341013'],
            'ม.1/4' => ['id' => '20104', 'name' => 'นางรอกียะห์', 'last' => 'สาแม', 'phone' => '0812341014'],

            // ม.2
            'ม.2/1' => ['id' => '20201', 'name' => 'นายอานนท์', 'last' => 'บุณยรัตน์', 'phone' => '0812342011'],
            'ม.2/2' => ['id' => '20202', 'name' => 'นางสาวมัสตูเราะห์', 'last' => 'เจ๊ะมะ', 'phone' => '0812342012'],
            'ม.2/3' => ['id' => '20203', 'name' => 'นายเกรียงไกร', 'last' => 'ชนะภัย', 'phone' => '0812342013'],
            'ม.2/4' => ['id' => '20204', 'name' => 'นางสาวฟาตีมา', 'last' => 'ดอเลาะ', 'phone' => '0812342014'],

            // ม.3
            'ม.3/1' => ['id' => '0640101', 'name' => 'นายดุลยามาน', 'last' => 'สามาลาดอ', 'phone' => '0812343011'], // existing teacher
            'ม.3/2' => ['id' => '20302', 'name' => 'นายประเสริฐ', 'last' => 'สุวรรณฉวี', 'phone' => '0812343012'],
            'ม.3/3' => ['id' => '20303', 'name' => 'นางสาวรอฮานา', 'last' => 'สาและ', 'phone' => '0812343013'],
            'ม.3/4' => ['id' => '20304', 'name' => 'นายเอกลักษณ์', 'last' => 'รัตนเดช', 'phone' => '0812343014'],

            // ม.4
            'ม.4/1' => ['id' => '0640102', 'name' => 'นายดาเนียล', 'last' => 'ดาลี', 'phone' => '0812344011'], // existing teacher
            'ม.4/2' => ['id' => '03401',   'name' => 'นายอิลยัส', 'last' => 'เมะสุ', 'phone' => '0812344012'], // existing teacher
            'ม.4/3' => ['id' => '20403', 'name' => 'นายศิริชัย', 'last' => 'เลิศอนันต์', 'phone' => '0812344013'],
            'ม.4/4' => ['id' => '20404', 'name' => 'นางสาวอามีนา', 'last' => 'ยามา', 'phone' => '0812344014'],

            // ม.5
            'ม.5/1' => ['id' => '20501', 'name' => 'นายธนาธิป', 'last' => 'ทองประสิทธิ์', 'phone' => '0812345011'],
            'ม.5/2' => ['id' => '20502', 'name' => 'นางสาวกัลยาณี', 'last' => 'วัฒนวงศ์', 'phone' => '0812345012'],
            'ม.5/3' => ['id' => '20503', 'name' => 'นายสิทธิชัย', 'last' => 'บุญประเสริฐ', 'phone' => '0812345013'],
            'ม.5/4' => ['id' => '20504', 'name' => 'นางสาวรุสมีนา', 'last' => 'มะดากะกุล', 'phone' => '0812345014'],

            // ม.6
            'ม.6/1' => ['id' => '20601', 'name' => 'นายพงศ์พิพัฒน์', 'last' => 'สุขสวัสดิ์', 'phone' => '0812346011'],
            'ม.6/2' => ['id' => '20602', 'name' => 'นางสาวฮาซานา', 'last' => 'หะยีอาแว', 'phone' => '0812346012'],
            'ม.6/3' => ['id' => '20603', 'name' => 'นายธีรเดช', 'last' => 'รักษาสัตย์', 'phone' => '0812346013'],
            'ม.6/4' => ['id' => '20604', 'name' => 'นางสาวสุภาพร', 'last' => 'นิมิตรวงศ์', 'phone' => '0812346014'],
        ];

        foreach ($teacherConfigs as $classroom => $cfg) {
            $username = $cfg['id'];
            $cleanRoom = str_replace('ม.', '', $classroom);

            $numericId = preg_replace('/[^0-9]/', '', $username);
            $citizenId = '39501' . str_pad($numericId ?: '1000', 8, '0', STR_PAD_LEFT);

            // 1. Find or create user
            $user = User::where('Username', $username)->first();
            if (!$user) {
                $user = User::create([
                    'UserID'    => (string) Str::uuid(),
                    'Username'  => $username,
                    'CitizenID' => $citizenId,
                    'Password'  => Hash::make("Teacher{$username}"),
                    'FirstName' => $cfg['name'],
                    'LastName'  => $cfg['last'],
                    'Role'      => 'ครู',
                    'Email'     => "teacher_{$username}@yru.ac.th",
                    'Phone'     => $cfg['phone'],
                    'Status'    => 'ปกติ',
                ]);
            } else {
                $user->update([
                    'Role'      => 'ครู',
                    'CitizenID' => $user->CitizenID ?: $citizenId,
                    'Status'    => 'ปกติ',
                ]);
            }

            // 2. Find or create Teacher model
            $teacher = Teacher::where('TeacherID', $username)
                ->orWhere('UserID', $user->UserID)
                ->first();

            if (!$teacher) {
                $teacher = Teacher::create([
                    'TeacherID' => $username,
                    'UserID'    => $user->UserID,
                ]);
            }

            // 3. Assign single clean Advisory Room (e.g. '1/1')
            TeacherAdvisoryRoom::where('TeacherID', $teacher->TeacherID)->delete();
            TeacherAdvisoryRoom::create([
                'TeacherID' => $teacher->TeacherID,
                'Classroom' => $cleanRoom,
            ]);
        }
    }
}
