<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $idMapping = [
            '10001' => '6910101',
            '10002' => '6910102',
            '10003' => '6910201',
            '10004' => '6910103',
            '10005' => '6920101',
            '10006' => '6930101',
        ];

        foreach ($idMapping as $oldId => $newId) {
            $oldIdStr = (string)$oldId;
            $newIdStr = (string)$newId;

            // Update users table for usernames
            DB::statement("UPDATE users SET Username = ? WHERE Username = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE users SET Username = ? WHERE Username = ?", ['std' . $newIdStr, 'std' . $oldIdStr]);
            DB::statement("UPDATE users SET Email = ? WHERE Email = ?", [$newIdStr . '@example.com', $oldIdStr . '@example.com']);

            // Update parent emails/usernames if any are like prt10001
            DB::statement("UPDATE users SET Username = ? WHERE Username = ?", ['prt' . $newIdStr, 'prt' . $oldIdStr]);
            DB::statement("UPDATE users SET Email = ? WHERE Email = ?", ['prt' . $newIdStr . '@example.com', 'prt' . $oldIdStr . '@example.com']);
            DB::statement("UPDATE parents SET Email = ? WHERE Email = ?", ['prt' . $newIdStr . '@example.com', 'prt' . $oldIdStr . '@example.com']);

            // Update students
            DB::statement("UPDATE students SET StudentID = ?, Photo = ? WHERE StudentID = ?", [$newIdStr, 'photos/' . $newIdStr . '.png', $oldIdStr]);

            // Update other tables referencing StudentID
            DB::statement("UPDATE parents SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE behavior_records SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE appeals SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE attendances SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE informant_reports SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE prayer_records SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE prayer_corrections SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $idMapping = [
            '6910101' => '10001',
            '6910102' => '10002',
            '6910201' => '10003',
            '6910103' => '10004',
            '6920101' => '10005',
            '6930101' => '10006',
        ];

        foreach ($idMapping as $newId => $oldId) {
            $oldIdStr = (string)$oldId;
            $newIdStr = (string)$newId;

            DB::statement("UPDATE users SET Username = ? WHERE Username = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE users SET Username = ? WHERE Username = ?", ['std' . $newIdStr, 'std' . $oldIdStr]);
            DB::statement("UPDATE users SET Email = ? WHERE Email = ?", [$newIdStr . '@example.com', $oldIdStr . '@example.com']);

            DB::statement("UPDATE users SET Username = ? WHERE Username = ?", ['prt' . $newIdStr, 'prt' . $oldIdStr]);
            DB::statement("UPDATE users SET Email = ? WHERE Email = ?", ['prt' . $newIdStr . '@example.com', 'prt' . $oldIdStr . '@example.com']);
            DB::statement("UPDATE parents SET Email = ? WHERE Email = ?", ['prt' . $newIdStr . '@example.com', 'prt' . $oldIdStr . '@example.com']);

            DB::statement("UPDATE students SET StudentID = ?, Photo = ? WHERE StudentID = ?", [$newIdStr, 'photos/' . $newIdStr . '.png', $oldIdStr]);

            DB::statement("UPDATE parents SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE behavior_records SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE appeals SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE attendances SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE informant_reports SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE prayer_records SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
            DB::statement("UPDATE prayer_corrections SET StudentID = ? WHERE StudentID = ?", [$newIdStr, $oldIdStr]);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
};
