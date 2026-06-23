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
        Schema::create('messages', function (Blueprint $table) {
            $table->string('MessageID', 36)->primary();
            $table->string('SenderID', 36);
            $table->string('ReceiverID', 36);
            $table->text('Content');
            $table->dateTime('SentDate');
            $table->boolean('IsRead')->default(false);
            $table->string('AttachmentDir', 255)->nullable();
            $table->timestamps();

            $table->foreign('SenderID')->references('UserID')->on('users')->onDelete('cascade');
            $table->foreign('ReceiverID')->references('UserID')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
