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
        if (!Schema::hasColumn('informant_reports', 'semester_id')) {
            Schema::table('informant_reports', function (Blueprint $table) {
                $table->unsignedBigInteger('semester_id')->nullable()->after('ReportDate');
            });

            $activeSemesterId = DB::table('semesters')->where('is_active', true)->value('semester_id')
                ?: DB::table('semesters')->value('semester_id');

            if ($activeSemesterId) {
                DB::table('informant_reports')->whereNull('semester_id')->update(['semester_id' => $activeSemesterId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('informant_reports', 'semester_id')) {
            Schema::table('informant_reports', function (Blueprint $table) {
                $table->dropColumn('semester_id');
            });
        }
    }
};
