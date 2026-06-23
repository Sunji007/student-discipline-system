<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_staff', function (Blueprint $table) {
            $table->string('StaffID', 36)->primary();
            $table->string('UserID', 36);
            $table->string('Position', 100)->nullable();
            $table->enum('Level', ['บันทึกได้', 'อนุมัติผล/ตั้งค่า']);
            $table->timestamps();

            $table->foreign('UserID')
                  ->references('UserID')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_staff');
    }
};