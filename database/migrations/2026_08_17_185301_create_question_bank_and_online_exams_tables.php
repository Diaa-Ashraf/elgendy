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
        // 1. إضافة حقول إضافية للامتحان الإلكتروني في جدول exams القائم
        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('is_online')->default(false)->after('exam_type');
            $table->unsignedInteger('duration_minutes')->nullable()->after('is_online')->comment('مدة الامتحان بالدقائق');
            $table->timestamp('starts_at')->nullable()->after('duration_minutes');
            $table->timestamp('ends_at')->nullable()->after('starts_at');
            $table->unsignedInteger('pass_percentage')->default(50)->after('ends_at');
            $table->boolean('show_correct_answers_after_submission')->default(true)->after('pass_percentage');
            $table->boolean('shuffle_questions')->default(false)->after('show_correct_answers_after_submission');
            $table->string('status')->default('published')->after('shuffle_questions'); // draft, published, archived
        });

        // 2. جدول بنك الأسئلة (Questions Bank)
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('stage_id')->constrained('educational_stages')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('question_image')->nullable();
            $table->enum('type', ['single_choice', 'multiple_choice', 'true_false'])->default('single_choice');
            $table->json('options'); // [{'key': 'A', 'text': '...'}, {'key': 'B', 'text': '...'}]
            $table->json('correct_answers'); // ['A'] أو ['A', 'C']
            $table->text('explanation')->nullable()->comment('الشرح والتفسير العلمي للإجابة النموذجية');
            $table->decimal('default_marks', 5, 2)->default(1.00);
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
            $table->string('topic')->nullable()->comment('الموضوع / الدرس / الباب لتشخيص نقاط الضعف');
            $table->timestamps();

            $table->index(['subject_id', 'stage_id']);
            $table->index('topic');
            $table->index('difficulty');
        });

        // 3. جدول الربط بين الامتحان وأسئلته (Exam Questions Pivot)
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->decimal('marks', 5, 2)->default(1.00);
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();

            $table->unique(['exam_id', 'question_id']);
            $table->index(['exam_id', 'order']);
        });

        // 4. جدول جلسات ومحاولات أداء الاختبار الإلكتروني للطلاب (Online Exam Attempts)
        Schema::create('online_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('total_score', 6, 2)->default(0.00);
            $table->decimal('max_possible_score', 6, 2)->default(0.00);
            $table->decimal('percentage', 5, 2)->default(0.00);
            $table->boolean('passed')->default(false);
            $table->enum('status', ['in_progress', 'completed', 'timed_out'])->default('in_progress');
            $table->json('student_answers')->nullable(); // answers snapshot & calculated per-question score
            $table->timestamps();

            $table->index(['exam_id', 'student_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_exam_attempts');
        Schema::dropIfExists('exam_questions');
        Schema::dropIfExists('questions');

        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn([
                'is_online',
                'duration_minutes',
                'starts_at',
                'ends_at',
                'pass_percentage',
                'show_correct_answers_after_submission',
                'shuffle_questions',
                'status',
            ]);
        });
    }
};
