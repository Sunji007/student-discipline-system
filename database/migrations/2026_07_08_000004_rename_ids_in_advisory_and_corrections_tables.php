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
        // 1. Rename column 'id' to 'teacher_advisory_room_id' in teacher_advisory_rooms
        Schema::table('teacher_advisory_rooms', function (Blueprint $table) {
            $table->renameColumn('id', 'teacher_advisory_room_id');
        });

        // 2. Rename column 'id' to 'prayer_correction_id' in prayer_corrections
        Schema::table('prayer_corrections', function (Blueprint $table) {
            $table->renameColumn('id', 'prayer_correction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Rename column 'teacher_advisory_room_id' to 'id' in teacher_advisory_rooms
        Schema::table('teacher_advisory_rooms', function (Blueprint $table) {
            $table->renameColumn('teacher_advisory_room_id', 'id');
        });

        // 2. Rename column 'prayer_correction_id' to 'id' in prayer_corrections
        Schema::table('prayer_corrections', function (Blueprint $table) {
            $table->renameColumn('prayer_correction_id', 'id');
        });
    }
};
