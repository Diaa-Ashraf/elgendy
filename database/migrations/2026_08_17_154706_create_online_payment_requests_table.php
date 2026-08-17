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
        Schema::create('online_payment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->default('vodafone_cash'); // vodafone_cash, instapay, wallet
            $table->string('sender_phone')->nullable();
            $table->string('transaction_reference')->nullable();
            $table->string('receipt_image')->nullable();
            $table->enum('type', ['session', 'month'])->default('month');
            $table->date('period_month')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index('created_at');
        });

        // تعديل عمود payment_method في student_payments ليكون string أو يقبل الطرق الجديدة
        Schema::table('student_payments', function (Blueprint $table) {
            $table->string('payment_method')->default('cash')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('online_payment_requests');
    }
};
