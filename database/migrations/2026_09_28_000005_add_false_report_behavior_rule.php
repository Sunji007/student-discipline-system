<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Rule สำหรับตัดคะแนนผู้ที่แจ้งเบาะแสเท็จเพื่อกลั่นแกล้ง (ใช้ร่วมกับระบบแจ้งเบาะแส)
    public const RULE_ID = 'f4a7c2d9-8e3b-4c6a-9d5e-1b2f3a4c5d6e';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('behavior_rules')) {
            return;
        }

        if (\App\Models\BehaviorRule::where('RuleID', self::RULE_ID)->exists()) {
            return;
        }

        \App\Models\BehaviorRule::create([
            'RuleID'         => self::RULE_ID,
            'RuleName'       => 'แจ้งข้อมูลอันเป็นเท็จต่อฝ่ายปกครองเพื่อกลั่นแกล้งหรือใส่ร้ายผู้อื่น',
            'RuleType'       => 'ตัดคะแนน',
            'ScoreModifier'  => -10,
            'Category'       => 'ความประพฤติและกริยามารยาท',
            'Description'    => 'การแจ้งเบาะแสพฤติกรรมโดยทราบว่าข้อมูลไม่เป็นความจริง เพื่อให้ผู้อื่นถูกตัดคะแนนหรือเสียชื่อเสียง ตรวจสอบได้จากผลการตรวจสอบเรื่องแจ้งเบาะแส (ผล: แจ้งเท็จโดยเจตนา)',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('behavior_rules')) {
            return;
        }

        \App\Models\BehaviorRule::where('RuleID', self::RULE_ID)
            ->whereDoesntHave('records')
            ->delete();
    }
};
