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
        Schema::create('salaries', function (Blueprint $table) {
           $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['fixed', 'percentage']);
            $table->decimal('base_amount', 8, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->date('month');
            $table->decimal('amount_paid', 8, 2);
            $table->date('paid_at');
            $table->timestamps();

            $table->index(['user_id', 'month']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salaries');
    }
};
