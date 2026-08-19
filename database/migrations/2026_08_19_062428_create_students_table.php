<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            // Optional link to a login account (student portal access)
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('student_uid')->unique(); // admission/roll number
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();

            $table->string('course')->nullable();
            $table->string('department')->nullable();
            $table->string('session')->nullable(); // e.g. 2024-2025

            $table->text('address')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('document_path')->nullable(); // NID/birth cert etc.

            $table->date('admission_date')->nullable();
            $table->enum('status', ['active', 'inactive', 'left'])->default('active');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};