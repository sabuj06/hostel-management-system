<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mess_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained('hostels')->cascadeOnDelete();
            $table->decimal('rate_per_day', 8, 2)->default(0);
            $table->timestamps();

            $table->unique('hostel_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mess_rates');
    }
};