<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. جدول الواجبات المنزلية
        Schema::create('homeworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('group_id')->nullable()->constrained('groups')->nullOnDelete();
            $table->foreignId('stage_id')->constrained('educational_stages')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->enum('type', ['questions', 'file_upload', 'mixed'])->default('questions');
            $table->string('attachment')->nullable()->comment('ملف PDF أو صورة مرفقة بالواجب');
            $table->datetime('due_date');
            $table->datetime('published_at')->nullable()->comment('null = مسودة');
            $table->decimal('total_marks', 5, 2)->default(10.00);
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->boolean('allow_late_submission')->default(false);
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->timestamps();

            $table->index(['tenant_id', 'stage_id']);
            $table->index(['tenant_id', 'status']);
            $table->index('due_date');
        });

        // 2. جدول ربط الواجب بأسئلة بنك الأسئلة
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

        // 3. جدول تسليمات الطلاب
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('homework_id')->constrained('homeworks')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->datetime('submitted_at')->nullable();
            $table->boolean('is_late')->default(false);
            $table->json('student_answers')->nullable()->comment('إجابات الأسئلة الاختيارية');
            $table->string('attachment')->nullable()->comment('ملف الحل المرفوع');
            $table->text('notes')->nullable()->comment('ملاحظات الطالب');
            $table->decimal('score', 5, 2)->nullable()->comment('الدرجة النهائية');
            $table->decimal('auto_score', 5, 2)->nullable()->comment('الدرجة التلقائية');
            $table->text('teacher_feedback')->nullable();
            $table->enum('status', ['pending', 'submitted', 'graded', 'returned'])->default('pending');
            $table->datetime('graded_at')->nullable();
            $table->timestamps();

            $table->unique(['homework_id', 'student_id']);
            $table->index(['tenant_id', 'student_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homework_questions');
        Schema::dropIfExists('homeworks');
    }
};
