<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Convert text values to integer characters first
        DB::statement("UPDATE parents SET Relationship = '1' WHERE Relationship = 'พ่อ'");
        DB::statement("UPDATE parents SET Relationship = '2' WHERE Relationship = 'แม่'");
        DB::statement("UPDATE parents SET Relationship = '3' WHERE Relationship = 'ญาติ'");

        DB::statement("UPDATE discipline_staff SET Level = '1' WHERE Level = 'บันทึกได้'");
        DB::statement("UPDATE discipline_staff SET Level = '2' WHERE Level = 'อนุมัติผล/ตั้งค่า'");

        // 2. Change column types to TINYINT using raw SQL for maximum compatibility
        DB::statement("ALTER TABLE parents MODIFY Relationship TINYINT NOT NULL");
        DB::statement("ALTER TABLE discipline_staff MODIFY Level TINYINT NOT NULL DEFAULT 1");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Modify columns back to VARCHAR / TEXT temporarily to clear TINYINT type check
        DB::statement("ALTER TABLE parents MODIFY Relationship VARCHAR(50) NOT NULL");
        DB::statement("ALTER TABLE discipline_staff MODIFY Level VARCHAR(50) NOT NULL DEFAULT 'บันทึกได้'");

        // 2. Map integer values back to labels
        DB::statement("UPDATE parents SET Relationship = 'พ่อ' WHERE Relationship = '1'");
        DB::statement("UPDATE parents SET Relationship = 'แม่' WHERE Relationship = '2'");
        DB::statement("UPDATE parents SET Relationship = 'ญาติ' WHERE Relationship = '3'");

        DB::statement("UPDATE discipline_staff SET Level = 'บันทึกได้' WHERE Level = '1'");
        DB::statement("UPDATE discipline_staff SET Level = 'อนุมัติผล/ตั้งค่า' WHERE Level = '2'");

        // 3. Put ENUM definitions back
        DB::statement("ALTER TABLE parents MODIFY Relationship ENUM('พ่อ', 'แม่', 'ญาติ') NOT NULL");
        DB::statement("ALTER TABLE discipline_staff MODIFY Level ENUM('บันทึกได้', 'อนุมัติผล/ตั้งค่า') NOT NULL DEFAULT 'บันทึกได้'");
    }
};
