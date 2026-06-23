<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->string('StudentID', 10)->primary();
            $table->string('UserID', 36);
            $table->string('ParentID', 36)->nullable();
            $table->string('FullName', 100);
            $table->string('GradeLevel', 10)->nullable();
            $table->string('Classroom', 10)->nullable();
            $table->integer('BehaviorScore')->default(100);
            $table->string('RiskStatus', 50)->default('ปกติ');
            $table->timestamps();

            $table->foreign('UserID')
                  ->references('UserID')
                  ->on('users')
                  ->onDelete('cascade');

            $table->foreign('ParentID')
                  ->references('ParentID')
                  ->on('parents')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
