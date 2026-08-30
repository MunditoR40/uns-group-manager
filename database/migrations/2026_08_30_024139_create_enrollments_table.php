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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('practice_group_id')->constrained()->cascadeOnDelete();
            $table->boolean('has_laptop')->default(false);
            $table->boolean('teacher_authorized')->default(false);
            $table->dateTime('enrolled_at'); 
            $table->enum('status', ['original', 'reasignado'])->default('original');
            $table->timestamps();

            $table->index('enrolled_at');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
