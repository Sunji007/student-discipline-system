<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. สร้างตาราง departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id('department_id');
            $table->string('name', 100)->unique();
            $table->string('short_name', 50)->unique();
            $table->string('code', 2)->unique();
            $table->timestamps();
        });

        // 2. เพิ่มข้อมูลกลุ่มสาระพื้นฐานทั้ง 10 กลุ่ม
        $departments = [
            ['name' => 'กลุ่มสาระการเรียนรู้ภาษาไทย', 'short_name' => 'ภาษาไทย', 'code' => '01'],
            ['name' => 'กลุ่มสาระการเรียนรู้คณิตศาสตร์', 'short_name' => 'คณิตศาสตร์', 'code' => '02'],
            ['name' => 'กลุ่มสาระการเรียนรู้วิทยาศาสตร์และเทคโนโลยี', 'short_name' => 'วิทยาศาสตร์และเทคโนโลยี', 'code' => '03'],
            ['name' => 'กลุ่มสาระการเรียนรู้สังคมศึกษา ศาสนา และวัฒนธรรม', 'short_name' => 'สังคมศึกษา ศาสนา และวัฒนธรรม', 'code' => '04'],
            ['name' => 'กลุ่มสาระการเรียนรู้สุขศึกษาและพลศึกษา', 'short_name' => 'สุขศึกษาและพลศึกษา', 'code' => '05'],
            ['name' => 'กลุ่มสาระการเรียนรู้ศิลปะ', 'short_name' => 'ศิลปะ', 'code' => '06'],
            ['name' => 'กลุ่มสาระการเรียนรู้การงานอาชีพ', 'short_name' => 'การงานอาชีพ', 'code' => '07'],
            ['name' => 'กลุ่มสาระการเรียนรู้ภาษาต่างประเทศ', 'short_name' => 'ภาษาต่างประเทศ', 'code' => '08'],
            ['name' => 'กิจกรรมพัฒนาผู้เรียน', 'short_name' => 'กิจกรรมพัฒนาผู้เรียน', 'code' => '09'],
            ['name' => 'ฝ่ายบริหาร/สำนักงาน', 'short_name' => 'ฝ่ายบริหาร/สำนักงาน', 'code' => '10'],
        ];

        foreach ($departments as $dept) {
            $dept['created_at'] = now();
            $dept['updated_at'] = now();
            DB::table('departments')->insert($dept);
        }

        // 3. เพิ่มคอลัมน์ department_id ในตาราง teachers
        Schema::table('teachers', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('UserID');
        });

        // 4. Map ข้อมูลกลุ่มสาระของครูเดิมไปยัง department_id ใหม่
        $teachers = DB::table('teachers')->get();
        foreach ($teachers as $t) {
            if (!$t->Department) continue;

            $deptStr = trim($t->Department);
            $mappedShortName = match($deptStr) {
                'วิทยาศาสตร์' => 'วิทยาศาสตร์และเทคโนโลยี',
                'สังคมศึกษา' => 'สังคมศึกษา ศาสนา และวัฒนธรรม',
                'ภาษาอังกฤษ' => 'ภาษาต่างประเทศ',
                default => $deptStr
            };

            $deptRow = DB::table('departments')->where('short_name', $mappedShortName)->first();
            if ($deptRow) {
                DB::table('teachers')->where('TeacherID', $t->TeacherID)->update([
                    'department_id' => $deptRow->department_id
                ]);
            }
        }

        // 5. ตั้งค่า foreign key และลบฟิลด์ Department แบบเดิม
        Schema::table('teachers', function (Blueprint $table) {
            $table->foreign('department_id')
                  ->references('department_id')
                  ->on('departments')
                  ->onDelete('set null');
            
            $table->dropColumn('Department');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->string('Department', 100)->nullable()->after('UserID');
        });

        // ดึงข้อมูลชื่อย่อของกลุ่มสาระกลับมาบันทึกในฟิลด์ Department
        $teachers = DB::table('teachers')->get();
        foreach ($teachers as $t) {
            if (!$t->department_id) continue;

            $deptRow = DB::table('departments')->where('department_id', $t->department_id)->first();
            if ($deptRow) {
                DB::table('teachers')->where('TeacherID', $t->TeacherID)->update([
                    'Department' => $deptRow->short_name
                ]);
            }
        }

        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn('department_id');
        });

        Schema::dropIfExists('departments');
    }
};
