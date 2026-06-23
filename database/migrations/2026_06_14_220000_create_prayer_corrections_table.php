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
        Schema::create('prayer_corrections', function (Blueprint $table) {
            $table->id();
            $table->string('StudentID', 10);
            $table->integer('Year');
            $table->integer('Month');
            $table->string('Status', 50)->default('แก้ละหมาดแล้ว');
            $table->string('RecordedBy', 36);
            $table->timestamps();

            $table->foreign('StudentID')->references('StudentID')->on('students')->onDelete('cascade');
            $table->foreign('RecordedBy')->references('UserID')->on('users')->onDelete('cascade');
            $table->unique(['StudentID', 'Year', 'Month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prayer_corrections');
    }
};
