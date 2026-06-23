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
        Schema::create('prayer_records', function (Blueprint $table) {
            $table->string('PrayerRecordID', 36)->primary();
            $table->string('StudentID', 10);
            $table->date('RecordDate');
            $table->time('RecordTime');
            $table->string('Period', 20); // เที่ยง, บ่าย
            $table->string('Status', 50); // ละหมาด, ละหมาดไม่ได้
            $table->string('RecordedBy', 36);
            $table->timestamps();

            $table->foreign('StudentID')->references('StudentID')->on('students')->onDelete('cascade');
            $table->foreign('RecordedBy')->references('UserID')->on('users')->onDelete('cascade');
            $table->unique(['StudentID', 'RecordDate', 'Period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_records');
    }
};
