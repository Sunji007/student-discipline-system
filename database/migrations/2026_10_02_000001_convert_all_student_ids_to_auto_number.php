<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        // Fetch all students ordered by GradeLevel, Classroom, StudentID
        $students = DB::table('students')
            ->orderBy('GradeLevel')
            ->orderBy('Classroom')
            ->orderBy('StudentID')
            ->get();

        if ($students->isNotEmpty()) {
            $needsMigration = false;
            foreach ($students as $s) {
                if (!preg_match('/^06\d{3,}$/', (string)$s->StudentID)) {
                    $needsMigration = true;
                    break;
                }
            }

            if ($needsMigration) {
                $idMap = [];
                $counter = 6000;
                foreach ($students as $s) {
                    $oldId = (string)$s->StudentID;
                    $newId = str_pad((string)$counter++, 5, '0', STR_PAD_LEFT);
                    $idMap[$oldId] = $newId;
                }

                // Step A: Update to intermediate temporary IDs to prevent unique constraint collisions
                foreach ($idMap as $oldId => $newId) {
                    $tempId = 'T' . $newId;

                    DB::table('students')->where('StudentID', $oldId)->update(['StudentID' => $tempId]);
                    DB::table('users')->where('Username', $oldId)->update(['Username' => $tempId]);
                    DB::table('users')->where('Username', 'std' . $oldId)->update(['Username' => 'std' . $tempId]);
                    DB::table('parents')->where('StudentID', $oldId)->update(['StudentID' => $tempId]);
                    DB::table('behavior_records')->where('StudentID', $oldId)->update(['StudentID' => $tempId]);
                    DB::table('appeals')->where('StudentID', $oldId)->update(['StudentID' => $tempId]);
                    DB::table('attendances')->where('StudentID', $oldId)->update(['StudentID' => $tempId]);
                    DB::table('informant_reports')->where('StudentID', $oldId)->update(['StudentID' => $tempId]);
                    DB::table('prayer_records')->where('StudentID', $oldId)->update(['StudentID' => $tempId]);
                    DB::table('prayer_corrections')->where('StudentID', $oldId)->update(['StudentID' => $tempId]);
                }

                // Step B: Finalize from temporary IDs to clean new 06xxx IDs
                foreach ($idMap as $oldId => $newId) {
                    $tempId = 'T' . $newId;

                    DB::table('students')->where('StudentID', $tempId)->update(['StudentID' => $newId]);
                    DB::table('users')->where('Username', $tempId)->update(['Username' => $newId]);
                    DB::table('users')->where('Username', 'std' . $tempId)->update(['Username' => 'std' . $newId]);
                    DB::table('parents')->where('StudentID', $tempId)->update(['StudentID' => $newId]);
                    DB::table('behavior_records')->where('StudentID', $tempId)->update(['StudentID' => $newId]);
                    DB::table('appeals')->where('StudentID', $tempId)->update(['StudentID' => $newId]);
                    DB::table('attendances')->where('StudentID', $tempId)->update(['StudentID' => $newId]);
                    DB::table('informant_reports')->where('StudentID', $tempId)->update(['StudentID' => $newId]);
                    DB::table('prayer_records')->where('StudentID', $tempId)->update(['StudentID' => $newId]);
                    DB::table('prayer_corrections')->where('StudentID', $tempId)->update(['StudentID' => $newId]);
                }
            }
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }
    }

    public function down(): void
    {
        // One-way migration
    }
};
