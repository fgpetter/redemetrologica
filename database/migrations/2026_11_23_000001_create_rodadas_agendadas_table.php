<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rodadas_agendadas', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->date('data')->unique();
            $table->string('status');
            $table->json('etapas');
            $table->timestamp('iniciada_em');
            $table->timestamp('concluida_em')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rodadas_agendadas');
    }
};
