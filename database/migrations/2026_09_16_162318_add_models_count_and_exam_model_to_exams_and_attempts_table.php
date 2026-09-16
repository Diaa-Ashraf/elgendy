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
        Schema::table('exams', function (Blueprint $table) {
            if (! Schema::hasColumn('exams', 'models_count')) {
                $table->unsignedTinyInteger('models_count')->default(1)->after('shuffle_questions')->comment('عدد النماذج (1=أ، 2=أ/ب، 3=أ/ب/ج)');
            }
        });

        Schema::table('online_exam_attempts', function (Blueprint $table) {
            if (! Schema::hasColumn('online_exam_attempts', 'exam_model')) {
                $table->string('exam_model', 20)->default('أ')->after('exam_id')->comment('نموذج الاختبار المعين للطالب (أ، ب، ج، د)');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'models_count')) {
                $table->dropColumn('models_count');
            }
        });

        Schema::table('online_exam_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('online_exam_attempts', 'exam_model')) {
                $table->dropColumn('exam_model');
            }
        });
    }
};
