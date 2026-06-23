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
        Schema::create('behavior_records', function (Blueprint $table) {
            $table->string('RecordID', 36)->primary();
            $table->string('StudentID', 10);
            $table->string('RuleID', 36);
            $table->date('RecordDate');
            $table->text('Description')->nullable();
            $table->string('RecordedBy', 36);
            $table->string('Status', 50)->default('รออนุมัติ'); // รออนุมัติ, อนุมัติแล้ว, ยกเลิก
            $table->string('Penalty', 100)->nullable();
            $table->timestamps();

            $table->foreign('StudentID')->references('StudentID')->on('students')->onDelete('cascade');
            $table->foreign('RuleID')->references('RuleID')->on('behavior_rules')->onDelete('cascade');
            $table->foreign('RecordedBy')->references('UserID')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('behavior_records');
    }
};
