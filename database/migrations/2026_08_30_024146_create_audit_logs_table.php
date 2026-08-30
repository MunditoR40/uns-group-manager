<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->uuid('batch_id')->index(); // Identificador único por lote de reasignación
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Delegado/Admin que ejecuta
            $table->string('action_type'); // ej: REALLOCATION, MANUAL_MOVE, ROLLBACK
            $table->json('previous_state'); // Estado previo en formato JSON
            $table->json('new_state'); // Estado nuevo en formato JSON
            $table->text('description')->nullable();
            $table->boolean('is_reverted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};