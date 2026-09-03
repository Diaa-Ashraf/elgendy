<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alter status enum to include 'postponed'
        DB::statement("ALTER TABLE `group_sessions` MODIFY COLUMN `status` ENUM('scheduled', 'held', 'cancelled', 'postponed') NOT NULL DEFAULT 'scheduled'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE `group_sessions` MODIFY COLUMN `status` ENUM('scheduled', 'held', 'cancelled') NOT NULL DEFAULT 'scheduled'");
    }
};
