<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('student_promotions')) {
            Schema::create('student_promotions', function (Blueprint $table) {
                $table->id('promotion_id');
                $table->integer('academic_year')->unique();
                $table->unsignedBigInteger('semester_id')->nullable();
                $table->unsignedBigInteger('evaluated_semester_id')->nullable();
                $table->integer('promoted_count')->default(0);
                $table->integer('graduated_count')->default(0);
                $table->integer('retained_count')->default(0);
                $table->timestamp('promoted_at')->nullable();
                $table->timestamps();
            });
        }

        // บันทึกปีการศึกษาปัจจุบันและปีก่อนหน้าในระบบ เพื่อป้องกันการเลื่อนชั้นซ้ำซ้อนในรอบปีปัจจุบันหรือการย้อนกลับไปเปิดปีเก่า
        if (Schema::hasTable('student_promotions')) {
            $existingActiveYear = DB::table('semesters')->where('is_active', true)->value('academic_year') ?? 2569;
            $allYears = DB::table('semesters')->pluck('academic_year')->push($existingActiveYear)->unique();
            foreach ($allYears as $y) {
                $y = (int) $y;
                if ($y <= $existingActiveYear && !DB::table('student_promotions')->where('academic_year', $y)->exists()) {
                    DB::table('student_promotions')->insert([
                        'academic_year' => $y,
                        'promoted_at'   => now(),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_promotions');
    }
};
