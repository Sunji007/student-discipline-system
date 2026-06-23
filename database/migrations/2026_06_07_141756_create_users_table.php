<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('UserID', 36)->primary();
            $table->string('Username', 50)->unique();
            $table->string('Password', 255);
            $table->string('FullName', 100);
            $table->string('Role', 50); // admin, discipline, teacher, student, parent
            $table->string('Status', 50)->default('ปกติ');
            $table->string('AdditionalInfo', 255)->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};