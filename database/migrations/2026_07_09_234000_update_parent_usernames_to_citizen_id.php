<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get all parents
        $parents = DB::table('users')->where('Role', 'ผู้ปกครอง')->get();

        foreach ($parents as $parent) {
            if ($parent->CitizenID && strlen($parent->CitizenID) === 13) {
                // Check if this CitizenID is already used as Username by someone else
                $exists = DB::table('users')
                    ->where('Username', $parent->CitizenID)
                    ->where('UserID', '!=', $parent->UserID)
                    ->exists();

                if (!$exists) {
                    DB::table('users')
                        ->where('UserID', $parent->UserID)
                        ->update([
                            'Username'   => $parent->CitizenID,
                            'updated_at' => now(),
                        ]);
                }
            }
        }
    }

    public function down(): void
    {
        // Reverting to 50xxx is not strictly necessary or easily reversible without old state,
        // so leaving it as no-op or simple log.
    }
};
