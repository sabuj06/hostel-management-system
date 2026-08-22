<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_category_id')->constrained('asset_categories')->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable()->unique();
            $table->unsignedInteger('quantity_total')->default(1);
            $table->unsignedInteger('quantity_available')->default(1);
            $table->unsignedInteger('low_stock_threshold')->default(0);
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->enum('status', ['active', 'damaged', 'written_off'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};