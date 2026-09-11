<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('group_sessions', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('topic');
            $table->text('homework_notes')->nullable()->after('notes');
        });

        Schema::create('parent_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('general'); // attendance, exam, payment, homework, warning, general
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->string('action_url')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'is_read']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_notifications');
        Schema::table('group_sessions', function (Blueprint $table) {
            $table->dropColumn(['notes', 'homework_notes']);
        });
    }
};
