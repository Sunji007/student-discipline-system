<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parents', function (Blueprint $table) {
            $table->string('ParentID', 36)->primary();
            $table->string('UserID', 36);
            // StudentID จะเพิ่มผ่าน FK หลัง students ถูกสร้าง
            // ใช้ nullable เพื่อสร้างตารางก่อนได้
            $table->string('StudentID', 10)->nullable();
            $table->enum('Relationship', ['พ่อ', 'แม่', 'ญาติ']);
            $table->string('FullName', 100);
            $table->string('Phone', 15)->nullable();
            $table->string('Email', 100)->nullable();
            $table->text('Address')->nullable();
            $table->timestamps();

            $table->foreign('UserID')
                  ->references('UserID')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
