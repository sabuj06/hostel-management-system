<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->enum('channel', ['email', 'sms']);
            $table->string('recipient'); // email address or phone number
            $table->string('subject')->nullable(); // used for email
            $table->text('message');
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error')->nullable();

            // Polymorphic-ish link to the source (Notice, Complaint, Invoice, etc.)
            $table->string('related_type')->nullable();
            $table->unsignedBigInteger('related_id')->nullable();

            $table->foreignId('triggered_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['related_type', 'related_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};