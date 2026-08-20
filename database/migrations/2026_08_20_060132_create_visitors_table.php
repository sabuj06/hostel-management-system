<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('gate_pass_no')->unique();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();

            $table->string('visitor_name');
            $table->string('phone')->nullable();
            $table->enum('relation', ['father', 'mother', 'brother', 'sister', 'relative', 'friend', 'other'])->default('relative');
            $table->string('purpose')->nullable();

            $table->string('id_proof_type')->nullable();   // e.g. NID, Student ID
            $table->string('id_proof_number')->nullable();

            $table->unsignedTinyInteger('total_visitors')->default(1); // group visits

            $table->dateTime('check_in_time');
            $table->dateTime('check_out_time')->nullable();
            $table->enum('status', ['checked_in', 'checked_out'])->default('checked_in');

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};