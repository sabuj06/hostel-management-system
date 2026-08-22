<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->enum('audience', ['all', 'students', 'staff', 'hostel'])->default('all');
            $table->foreignId('hostel_id')->nullable()->constrained('hostels')->nullOnDelete();
            $table->enum('priority', ['normal', 'important', 'urgent'])->default('normal');
            $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('publish_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};