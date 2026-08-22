<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('curfew_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->unique()->constrained()->cascadeOnDelete();
            $table->time('curfew_time')->default('22:00:00');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curfew_settings');
    }
};