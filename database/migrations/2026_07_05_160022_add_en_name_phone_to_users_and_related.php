<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add EN name fields to users table (if not exists)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'FirstName_EN')) {
                $table->string('FirstName_EN', 50)->nullable()->after('FirstName');
            }
            if (!Schema::hasColumn('users', 'LastName_EN')) {
                $table->string('LastName_EN', 50)->nullable()->after('LastName');
            }
        });

        // Add EN name fields to students table
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'FirstName_EN')) {
                $table->string('FirstName_EN', 50)->nullable()->after('FirstName');
            }
            if (!Schema::hasColumn('students', 'LastName_EN')) {
                $table->string('LastName_EN', 50)->nullable()->after('LastName');
            }
        });

        // Add EN name fields to parents table
        Schema::table('parents', function (Blueprint $table) {
            if (!Schema::hasColumn('parents', 'FirstName_EN')) {
                $table->string('FirstName_EN', 50)->nullable()->after('FirstName');
            }
            if (!Schema::hasColumn('parents', 'LastName_EN')) {
                $table->string('LastName_EN', 50)->nullable()->after('LastName');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'FirstName_EN')) $table->dropColumn('FirstName_EN');
            if (Schema::hasColumn('users', 'LastName_EN'))  $table->dropColumn('LastName_EN');
        });
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'FirstName_EN')) $table->dropColumn('FirstName_EN');
            if (Schema::hasColumn('students', 'LastName_EN'))  $table->dropColumn('LastName_EN');
        });
        Schema::table('parents', function (Blueprint $table) {
            if (Schema::hasColumn('parents', 'FirstName_EN')) $table->dropColumn('FirstName_EN');
            if (Schema::hasColumn('parents', 'LastName_EN'))  $table->dropColumn('LastName_EN');
        });
    }
};
