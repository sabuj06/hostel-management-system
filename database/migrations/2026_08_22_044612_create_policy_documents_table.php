<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_documents', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('file_path')->nullable(); // stored under storage/app/policy-documents
            $table->string('original_filename')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['processing', 'ready', 'failed'])->default('processing');
            $table->unsignedInteger('chunk_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_documents');
    }
};