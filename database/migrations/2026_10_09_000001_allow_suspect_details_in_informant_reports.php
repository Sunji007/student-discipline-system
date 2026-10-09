<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // This field contains descriptions or several IDs, not a single student reference.
        foreach (Schema::getForeignKeys('informant_reports') as $foreignKey) {
            if ($foreignKey['columns'] === ['StudentID']) {
                Schema::table('informant_reports', function (Blueprint $table) use ($foreignKey) {
                    $table->dropForeign($foreignKey['name'] ?: ['StudentID']);
                });
            }
        }

        // MySQL retains the foreign-key index; a full-width index cannot cover TEXT.
        foreach (Schema::getIndexes('informant_reports') as $index) {
            if ($index['columns'] === ['StudentID'] && !$index['primary']) {
                Schema::table('informant_reports', function (Blueprint $table) use ($index) {
                    $table->dropIndex($index['name']);
                });
            }
        }

        Schema::table('informant_reports', function (Blueprint $table) {
            $table->text('StudentID')->nullable()->change();
        });
    }

    public function down(): void
    {
        // One-way: restoring VARCHAR(10) and the FK could truncate or reject saved reports.
    }
};
