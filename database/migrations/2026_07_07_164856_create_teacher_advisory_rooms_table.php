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
        Schema::create('teacher_advisory_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('TeacherID', 36);
            $table->string('Classroom', 10);
            $table->timestamps();

            $table->foreign('TeacherID')
                  ->references('TeacherID')
                  ->on('teachers')
                  ->onDelete('cascade');
        });

        // Migrate existing advisory rooms data
        $teachers = DB::table('teachers')->get();
        foreach ($teachers as $teacher) {
            if (!empty($teacher->AdvisoryRoom)) {
                DB::table('teacher_advisory_rooms')->insert([
                    'TeacherID'  => $teacher->TeacherID,
                    'Classroom'  => $teacher->AdvisoryRoom,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            if (!empty($teacher->AdvisoryRoom2)) {
                DB::table('teacher_advisory_rooms')->insert([
                    'TeacherID'  => $teacher->TeacherID,
                    'Classroom'  => $teacher->AdvisoryRoom2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Drop columns from teachers table
        Schema::table('teachers', function (Blueprint $table) {
            $table->dropColumn(['AdvisoryRoom', 'AdvisoryRoom2']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add columns back
        Schema::table('teachers', function (Blueprint $table) {
            $table->string('AdvisoryRoom', 10)->nullable()->after('Department');
            $table->string('AdvisoryRoom2', 10)->nullable()->after('AdvisoryRoom');
        });

        // Restore data
        $advisoryRooms = DB::table('teacher_advisory_rooms')->get()->groupBy('TeacherID');
        foreach ($advisoryRooms as $teacherId => $rooms) {
            $updateData = [];
            if (isset($rooms[0])) {
                $updateData['AdvisoryRoom'] = $rooms[0]->Classroom;
            }
            if (isset($rooms[1])) {
                $updateData['AdvisoryRoom2'] = $rooms[1]->Classroom;
            }
            if (!empty($updateData)) {
                DB::table('teachers')->where('TeacherID', $teacherId)->update($updateData);
            }
        }

        Schema::dropIfExists('teacher_advisory_rooms');
    }
};
