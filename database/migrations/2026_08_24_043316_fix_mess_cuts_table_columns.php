<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mess_cuts', function (Blueprint $table) {
            if (! Schema::hasColumn('mess_cuts', 'student_id')) {
                $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            }
            if (! Schema::hasColumn('mess_cuts', 'from_date')) {
                $table->date('from_date')->nullable()->after('student_id');
            }
            if (! Schema::hasColumn('mess_cuts', 'to_date')) {
                $table->date('to_date')->nullable()->after('from_date');
            }
            if (! Schema::hasColumn('mess_cuts', 'reason')) {
                $table->string('reason')->nullable()->after('to_date');
            }
            if (! Schema::hasColumn('mess_cuts', 'marked_by')) {
                $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete()->after('reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mess_cuts', function (Blueprint $table) {
            if (Schema::hasColumn('mess_cuts', 'from_date')) {
                $table->dropColumn('from_date');
            }
            if (Schema::hasColumn('mess_cuts', 'to_date')) {
                $table->dropColumn('to_date');
            }
        });
    }
};