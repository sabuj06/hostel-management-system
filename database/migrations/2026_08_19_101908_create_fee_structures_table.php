<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Monthly Hostel Fee - Double Room
            $table->foreignId('hostel_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('room_type', ['single', 'double', 'triple', 'dormitory', 'any'])->default('any');
            $table->decimal('amount', 10, 2);
            $table->enum('frequency', ['monthly', 'yearly', 'one_time'])->default('monthly');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_structures');
    }
};