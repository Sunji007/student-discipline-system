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
        Schema::table('users', function (Blueprint $table) {
            $table->string('CitizenID', 13)->nullable()->after('Username');
        });
        Schema::table('parents', function (Blueprint $table) {
            $table->string('CitizenID', 13)->nullable()->after('LastName_EN');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('CitizenID');
        });
        Schema::table('parents', function (Blueprint $table) {
            $table->dropColumn('CitizenID');
        });
    }
};
