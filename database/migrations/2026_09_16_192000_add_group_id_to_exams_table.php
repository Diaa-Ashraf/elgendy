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
            if (! Schema::hasColumn('exams', 'group_id')) {
                $table->foreignId('group_id')
                    ->nullable()
                    ->after('stage_id')
                    ->constrained('groups')
                    ->nullOnDelete()
                    ->comment('المجموعة المستهدفة بالامتحان (فارغ = جميع طلاب المرحلة)');

                $table->index(['stage_id', 'group_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            if (Schema::hasColumn('exams', 'group_id')) {
                $table->dropConstrainedForeignId('group_id');
            }
        });
    }
};
