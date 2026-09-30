<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('appeals') && Schema::hasColumn('appeals', 'RestoredPoints')) {
            if (DB::getDriverName() === 'mysql') {
                // เติมคะแนนที่คืน (RestoredPoints) ให้กับคำร้องที่มีสถานะ "คืนคะแนน" แต่ยังมีค่าเป็น NULL หรือ 0
                // โดยอ้างอิงจากค่า ScoreModifier เดิมของกฎพฤติกรรมที่ถูกตัดคะแนน
                DB::statement("
                    UPDATE appeals a
                    JOIN behavior_records br ON a.RecordID = br.RecordID
                    JOIN behavior_rules r ON br.RuleID = r.RuleID
                    SET a.RestoredPoints = ABS(r.ScoreModifier)
                    WHERE a.Status = 'คืนคะแนน'
                      AND (a.RestoredPoints IS NULL OR a.RestoredPoints = 0)
                ");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ไม่ย้อนกลับค่า RestoredPoints เนื่องจากเป็นการซ่อมแซมข้อมูลทางประวัติศาสตร์ (Non-destructive backfill)
    }
};
