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
            $table->string('Phone', 10)->nullable()->change();
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->string('Phone', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('Phone', 20)->nullable()->change();
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->string('Phone', 15)->nullable()->change();
        });
    }
};
