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
        // 1. جدول الواجبات والتكليفات المنزلية (Homeworks)
        if (! Schema::hasTable('homeworks')) {
            Schema::create('homeworks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->foreignId('stage_id')->constrained('educational_stages')->cascadeOnDelete();
                $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
                $table->enum('type', ['questions', 'file_upload', 'mixed'])->default('questions');
                $table->string('attachment')->nullable()->comment('ملف الواجب المرفق PDF أو صورة');
                $table->dateTime('due_date')->comment('آخر موعد للتسليم');
                $table->dateTime('published_at')->nullable()->comment('تاريخ ووقت النشر');
                $table->decimal('total_marks', 6, 2)->default(10.00);
                $table->enum('status', ['draft', 'published', 'closed'])->default('published');
                $table->boolean('allow_late_submission')->default(false);
                $table->unsignedTinyInteger('max_attempts')->default(1);
                $table->timestamps();

                $table->index(['stage_id', 'status']);
                $table->index(['group_id', 'status']);
                $table->index('due_date');
            });
        }

        // 2. جدول الربط بين الواجب وبنك الأسئلة (Homework Questions Pivot)
        if (! Schema::hasTable('homework_questions')) {
            Schema::create('homework_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('homework_id')->constrained('homeworks')->cascadeOnDelete();
                $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
                $table->decimal('marks', 5, 2)->default(1.00);
                $table->unsignedInteger('order')->default(1);
                $table->timestamps();

                $table->unique(['homework_id', 'question_id']);
                $table->index(['homework_id', 'order']);
            });
        }

        // 3. جدول تسليمات الطلاب للواجبات (Homework Submissions)
        if (! Schema::hasTable('homework_submissions')) {
            Schema::create('homework_submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('homework_id')->constrained('homeworks')->cascadeOnDelete();
                $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
                $table->dateTime('submitted_at')->nullable();
                $table->dateTime('graded_at')->nullable();
                $table->boolean('is_late')->default(false);
                $table->enum('status', ['pending', 'submitted', 'graded', 'returned'])->default('pending');
                $table->json('student_answers')->nullable()->comment('إجابات الأسئلة الاختيارية والتصحيح التلقائي');
                $table->string('attachment')->nullable()->comment('ملف أو صورة الحل المرفوعة من الطالب');
                $table->text('notes')->nullable()->comment('ملاحظات الطالب');
                $table->decimal('score', 6, 2)->nullable()->comment('الدرجة النهائية المرصودة');
                $table->decimal('auto_score', 6, 2)->nullable()->comment('درجة الأسئلة الإلكترونية التلقائية');
                $table->text('teacher_feedback')->nullable()->comment('ملاحظات وتوجيهات المعلم');
                $table->timestamps();

                $table->index(['homework_id', 'student_id']);
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework_questions');
        Schema::dropIfExists('homeworks');
    }
};
