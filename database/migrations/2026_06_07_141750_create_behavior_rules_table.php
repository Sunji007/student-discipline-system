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
        Schema::create('behavior_rules', function (Blueprint $table) {
            $table->string('RuleID', 36)->primary();
            $table->string('RuleName', 100);
            $table->string('RuleType', 50); // ตัดคะแนน, เพิ่มคะแนน
            $table->integer('ScoreModifier');
            $table->string('Category', 100)->nullable();
            $table->text('Description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('behavior_rules');
    }
};
