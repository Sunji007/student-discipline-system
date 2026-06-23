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
        Schema::create('appeals', function (Blueprint $table) {
            $table->string('AppealID', 36)->primary();
            $table->string('RecordID', 36);
            $table->string('StudentID', 10);
            $table->text('Reason');
            $table->string('EvidencePath', 255)->nullable();
            $table->date('AppealDate');
            $table->string('Status', 50)->default('รอตรวจสอบ'); // รอตรวจสอบ, คืนคะแนน, ยกเลิกคำร้อง
            $table->string('ReviewerID', 36)->nullable();
            $table->date('ReviewDate')->nullable();
            $table->text('ReviewNotes')->nullable();
            $table->timestamps();

            $table->foreign('RecordID')->references('RecordID')->on('behavior_records')->onDelete('cascade');
            $table->foreign('StudentID')->references('StudentID')->on('students')->onDelete('cascade');
            $table->foreign('ReviewerID')->references('UserID')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appeals');
    }
};
