<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add semester_id columns (nullable initially so we can backfill)
        Schema::table('behavior_records', function (Blueprint $table) {
            $table->unsignedBigInteger('semester_id')->nullable()->after('RecordedBy');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->unsignedBigInteger('semester_id')->nullable()->after('RecordedBy');
        });

        Schema::table('prayer_records', function (Blueprint $table) {
            $table->unsignedBigInteger('semester_id')->nullable()->after('RecordedBy');
        });

        // Helper function to map record dates to Academic Year and Term
        $getSemesterDetails = function($dateStr) {
            $date = Carbon::parse($dateStr);
            $year = $date->year;
            $month = $date->month;
            
            if ($month >= 5 && $month <= 10) {
                // Term 1 (May to October)
                $academicYear = $year + 543;
                $term = 1;
            } else {
                // Term 2 (November to April)
                $academicYear = ($month <= 4 ? $year - 1 : $year) + 543;
                $term = 2;
            }
            return ['year' => $academicYear, 'term' => $term];
        };

        // Cache created semesters to avoid duplicate inserts
        $semestersCache = [];
        $getOrCreateSemester = function($academicYear, $term) use (&$semestersCache) {
            $key = "{$academicYear}_{$term}";
            if (isset($semestersCache[$key])) {
                return $semestersCache[$key];
            }
            
            $semId = DB::table('semesters')->where('academic_year', $academicYear)->where('term', $term)->value('id');
            if (!$semId) {
                $semId = DB::table('semesters')->insertGetId([
                    'academic_year' => $academicYear,
                    'term' => $term,
                    'is_active' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $semestersCache[$key] = $semId;
            return $semId;
        };

        // 2. Backfill existing records
        // behavior_records
        DB::table('behavior_records')->orderBy('RecordID')->chunk(100, function ($records) use ($getSemesterDetails, $getOrCreateSemester) {
            foreach ($records as $record) {
                $details = $getSemesterDetails($record->RecordDate);
                $semId = $getOrCreateSemester($details['year'], $details['term']);
                DB::table('behavior_records')->where('RecordID', $record->RecordID)->update(['semester_id' => $semId]);
            }
        });

        // attendances
        DB::table('attendances')->orderBy('AttendanceID')->chunk(100, function ($records) use ($getSemesterDetails, $getOrCreateSemester) {
            foreach ($records as $record) {
                $details = $getSemesterDetails($record->Date);
                $semId = $getOrCreateSemester($details['year'], $details['term']);
                DB::table('attendances')->where('AttendanceID', $record->AttendanceID)->update(['semester_id' => $semId]);
            }
        });

        // prayer_records
        DB::table('prayer_records')->orderBy('PrayerRecordID')->chunk(100, function ($records) use ($getSemesterDetails, $getOrCreateSemester) {
            foreach ($records as $record) {
                $details = $getSemesterDetails($record->RecordDate);
                $semId = $getOrCreateSemester($details['year'], $details['term']);
                DB::table('prayer_records')->where('PrayerRecordID', $record->PrayerRecordID)->update(['semester_id' => $semId]);
            }
        });

        // 3. Set the latest semester as active (default fallback)
        $activeExists = DB::table('semesters')->where('is_active', true)->exists();
        if (!$activeExists) {
            $latestId = DB::table('semesters')->orderBy('academic_year', 'desc')->orderBy('term', 'desc')->value('id');
            if ($latestId) {
                DB::table('semesters')->where('id', $latestId)->update(['is_active' => true]);
            } else {
                $currentYear = date('Y') + 543;
                DB::table('semesters')->insert([
                    'academic_year' => $currentYear,
                    'term' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Set up Foreign Keys
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('behavior_records', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });

        Schema::table('prayer_records', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn('semester_id');
        });
    }
};
