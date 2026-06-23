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
        Schema::create('informant_reports', function (Blueprint $table) {
            $table->string('ReportID', 36)->primary();
            $table->string('Title', 100)->nullable();
            $table->string('Category', 50)->nullable(); // e.g. ทะเลาะวิวาท, ยาเสพติด, หนีเรียน, การแต่งกาย, อื่นๆ
            $table->text('Description');
            $table->boolean('IsAnonymous')->default(false);
            $table->string('ReporterName', 100)->nullable(); // filled if not anonymous
            $table->string('ReporterID', 36)->nullable(); // UserID of reporter if logged in and not anonymous
            $table->string('StudentID', 10)->nullable(); // StudentID of suspected student if identified
            $table->string('EvidencePath', 255)->nullable();
            $table->string('Status', 50)->default('เรื่องใหม่'); // เรื่องใหม่, กำลังตรวจสอบ, ปิดเรื่องแล้ว
            $table->text('Remarks')->nullable(); // notes by discipline staff
            $table->dateTime('ReportDate')->nullable();
            $table->timestamps();

            $table->foreign('ReporterID')->references('UserID')->on('users')->onDelete('set null');
            $table->foreign('StudentID')->references('StudentID')->on('students')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informant_reports');
    }
};
