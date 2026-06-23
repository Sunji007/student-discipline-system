<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,          // 1. users, teachers
            StudentSeeder::class,       // 2. students, parents
            BehaviorRuleSeeder::class,  // 3. กฎเกณฑ์
            BehaviorRecordSeeder::class,// 4. บันทึกพฤติกรรม
            AttendanceSeeder::class,    // 5. การเข้าแถว
            RolePermissionSeeder::class,// 6. สิทธิ์
            InformantReportSeeder::class,// 7. แจ้งเบาะแส
        ]);
    }
}