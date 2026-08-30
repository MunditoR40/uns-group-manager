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
        Schema::create('practice_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theory_group_id')->constrained()->cascadeOnDelete();
            $table->string('code'); // P1A, P1B, P1C, etc.
            $table->unsignedSmallInteger('base_capacity')->default(15);
            $table->string('schedule')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('practice_groups');
    }
};
