<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appeals', function (Blueprint $table) {
            if (!Schema::hasColumn('appeals', 'RestoredPoints')) {
                $table->decimal('RestoredPoints', 5, 2)->nullable()->after('Status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appeals', function (Blueprint $table) {
            if (Schema::hasColumn('appeals', 'RestoredPoints')) {
                $table->dropColumn('RestoredPoints');
            }
        });
    }
};
