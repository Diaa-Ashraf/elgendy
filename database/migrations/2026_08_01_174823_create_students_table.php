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
        Schema::create('students', function (Blueprint $table) {
           $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('parent_phone');
            $table->text('address')->nullable();
            $table->foreignId('stage_id')->constrained('educational_stages')->restrictOnDelete();
            $table->enum('gender', ['male', 'female']);
            $table->date('birth_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('stage_id');
            $table->index('parent_phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
