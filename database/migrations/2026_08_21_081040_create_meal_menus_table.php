<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->nullable()->constrained('hostels')->cascadeOnDelete();
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner']);
            $table->string('items', 500);
            $table->timestamps();

            $table->unique(['hostel_id', 'day_of_week', 'meal_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_menus');
    }
};