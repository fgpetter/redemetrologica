<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agenda_interlabs', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->default(new Expression("(replace(left(uuid(),12),_utf8mb3'-',_utf8mb4'0'))"))->unique();
            $table->foreignId('interlab_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['AGENDADO', 'CONFIRMADO', 'CONCLUIDO'])->nullable();
            $table->enum('certificado', ['EMPRESA', 'PARTICIPANTE'])->nullable();
            $table->boolean('inscricao')->default(0);
            $table->boolean('site')->default(0);
            $table->boolean('destaque')->default(0);
            $table->text('descricao')->nullable();
            $table->text('inscricao_manual')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->decimal('valor_rs')->nullable();
            $table->decimal('valor_s_se')->nullable();
            $table->decimal('valor_co')->nullable();
            $table->decimal('valor_n_ne')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_interlabs');
    }
};
