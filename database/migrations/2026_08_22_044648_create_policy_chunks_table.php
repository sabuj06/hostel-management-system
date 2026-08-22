<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policy_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('policy_document_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('chunk_index');
            $table->text('content');
            $table->timestamps();

            // Powers the "R" (retrieval) in RAG — MySQL native full-text search,
            // no external vector DB needed.
            $table->fullText('content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('policy_chunks');
    }
};