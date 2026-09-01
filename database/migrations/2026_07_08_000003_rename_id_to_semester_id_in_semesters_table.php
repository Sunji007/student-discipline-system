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
        // 1. Drop foreign keys referencing semesters(id)
        Schema::table('behavior_records', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
        });

        Schema::table('prayer_records', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
        });

        // 2. Rename column 'id' to 'semester_id' in semesters table
        Schema::table('semesters', function (Blueprint $table) {
            $table->renameColumn('id', 'semester_id');
        });

        // 3. Recreate foreign keys referencing semesters(semester_id)
        Schema::table('behavior_records', function (Blueprint $table) {
            $table->foreign('semester_id')->references('semester_id')->on('semesters')->onDelete('restrict');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('semester_id')->references('semester_id')->on('semesters')->onDelete('restrict');
        });

        Schema::table('prayer_records', function (Blueprint $table) {
            $table->foreign('semester_id')->references('semester_id')->on('semesters')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop foreign keys referencing semesters(semester_id)
        Schema::table('behavior_records', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
        });

        Schema::table('prayer_records', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
        });

        // 2. Rename column 'semester_id' to 'id' in semesters table
        Schema::table('semesters', function (Blueprint $table) {
            $table->renameColumn('semester_id', 'id');
        });

        // 3. Recreate foreign keys referencing semesters(id)
        Schema::table('behavior_records', function (Blueprint $table) {
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('restrict');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('restrict');
        });

        Schema::table('prayer_records', function (Blueprint $table) {
            $table->foreign('semester_id')->references('id')->on('semesters')->onDelete('restrict');
        });
    }
};
