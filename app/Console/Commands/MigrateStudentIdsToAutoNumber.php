<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateStudentIdsToAutoNumber extends Command
{
    protected $signature = 'students:migrate-ids {--start=6000 : Starting number for student IDs}';
    protected $description = 'Migrate all existing student IDs across all tables to sequential auto-number format starting from 06000';

    public function handle()
    {
        $startNum = (int)$this->option('start');
        $this->info("Starting student ID migration from {$startNum}...");

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        }

        $students = DB::table('students')
            ->orderBy('GradeLevel')
            ->orderBy('Classroom')
            ->orderBy('StudentID')
            ->get();

        if ($students->isEmpty()) {
            $this->warn("No students found to migrate.");
            return 0;
        }

        $mappings = [];
        $counter = $startNum;
        foreach ($students as $s) {
            $mappings[] = [
                'old' => (string)$s->StudentID,
                'new' => str_pad((string)$counter++, 5, '0', STR_PAD_LEFT),
            ];
        }

        $this->info("Mapping " . count($mappings) . " students to new auto IDs...");

        // Step A: Intermediate temporary IDs
        foreach ($mappings as $map) {
            $oldId = (string)$map['old'];
            $newId = (string)$map['new'];
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

        // Step B: Final IDs
        foreach ($mappings as $map) {
            $oldId = (string)$map['old'];
            $newId = (string)$map['new'];
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

            $this->line("  {$oldId} -> {$newId}");
        }

        if ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } elseif ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        }

        $this->info("Migration completed successfully! Total: " . count($mappings) . " students.");
        return 0;
    }
}
