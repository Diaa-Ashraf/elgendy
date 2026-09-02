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
        if (Schema::hasTable('activity_log')) {
            Schema::table('activity_log', function (Blueprint $table) {
                if (! Schema::hasColumn('activity_log', 'tenant_id')) {
                    $table->foreignId('tenant_id')->nullable()->after('id')->constrained('tenants')->nullOnDelete();
                    $table->index(['tenant_id', 'created_at']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('activity_log') && Schema::hasColumn('activity_log', 'tenant_id')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropConstrainedForeignId('tenant_id');
            });
        }
    }
};
