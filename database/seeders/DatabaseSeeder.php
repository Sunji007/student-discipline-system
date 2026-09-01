<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Check if users already exist to avoid duplicate seed issues
        if (User::exists()) {
            return;
        }

        $this->call([
            UserSeeder::class,          // 1. users, teachers
            StudentSeeder::class,       // 2. students, parents
            BehaviorRuleSeeder::class,  // 3. กฎเกณฑ์
            BehaviorRecordSeeder::class,// 4. บันทึกพฤติกรรม
            AttendanceSeeder::class,    // 5. การเข้าแถว
            RolePermissionSeeder::class,// 6. สิทธิ์
            InformantReportSeeder::class,// 7. แจ้งเบาะแส
            AllClassroomsSeeder::class, // 8. ข้อมูลห้อง ม.1/1 ถึง ม.6/4
            AllTeachersSeeder::class,   // 9. ครูประจำชั้น 24 ห้องเรียน
        ]);
    }
}