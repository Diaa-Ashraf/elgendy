<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول الملازم والكتب
        Schema::create('study_materials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('stage_id')->nullable()->constrained('educational_stages')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->decimal('cost_price', 8, 2)->default(0); // تكلفة الطباعة
            $table->decimal('sale_price', 8, 2)->default(0); // سعر البيع للطالب
            $table->integer('stock_quantity')->default(0); // الكمية المتاحة بالمخزن
            $table->string('term')->nullable(); // الترم الأول / الترم الثاني / المراجعة النهائية
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // جدول عمليات تسليم الملازم للطلاب
        Schema::create('student_material_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('study_material_id')->constrained('study_materials')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('price', 8, 2);
            $table->decimal('paid_amount', 8, 2)->default(0);
            $table->enum('payment_status', ['paid', 'unpaid', 'partial'])->default('unpaid');
            $table->date('delivered_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_material_deliveries');
        Schema::dropIfExists('study_materials');
    }
};
