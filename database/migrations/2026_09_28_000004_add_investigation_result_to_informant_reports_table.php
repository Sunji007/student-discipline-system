<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('informant_reports', function (Blueprint $table) {
            // ผลการตรวจสอบเมื่อปิดเรื่อง: เป็นความจริง / ไม่พบหลักฐานเพียงพอ /
            // แจ้งเท็จโดยเจตนา / แจ้งไม่ถูกต้องโดยไม่เจตนา
            $table->string('InvestigationResult', 100)->nullable()->after('Remarks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('informant_reports', function (Blueprint $table) {
            $table->dropColumn('InvestigationResult');
        });
    }
};
