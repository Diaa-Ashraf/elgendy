<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('educational_stages')->cascadeOnDelete();
            $table->string('title');
            $table->date('date');
            $table->unsignedInteger('total_marks');
            $table->enum('exam_type', ['monthly', 'midterm', 'final', 'quiz'])->default('monthly');
            $table->timestamps();

            $table->index(['subject_id', 'stage_id']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
