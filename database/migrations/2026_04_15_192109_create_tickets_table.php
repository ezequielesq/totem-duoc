<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('rut', 12);
            $table->string('nombre');
            $table->string('motivo', 50);
            $table->string('ticket_numero', 10)->unique();
            $table->string('status', 20)->default('espera');
            $table->integer('mesa')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_at');
            $table->index('motivo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
