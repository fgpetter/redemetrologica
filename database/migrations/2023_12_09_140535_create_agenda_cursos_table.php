<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agenda_cursos', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->default(new Expression("(replace(left(uuid(),12),_utf8mb3'-',_utf8mb4'0'))"))->unique();
            $table->foreignId('curso_id')->constrained();
            $table->foreignId('instrutor_id')->constrained('instrutores', 'id');
            $table->foreignId('empresa_id')->constrained('pessoas', 'id')->nullable();
            $table->text('endereco_local')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->date('data_limite_pagamento')->nullable();
            $table->date('validade_proposta')->nullable();
            $table->string('horario')->nullable();
            $table->enum('status', ['AGENDADO', 'CANCELADO', 'CONFIRMADO', 'REALIZADO', 'PROPOSTA ENVIADA', 'REAGENDAR']);
            $table->enum('tipo_agendamento', ['ONLINE', 'EVENTO', 'IN-COMPANY']);
            $table->boolean('destaque')->default(0);
            $table->boolean('inscricoes')->default(0);
            $table->boolean('site')->default(0);
            $table->integer('carga_horaria')->nullable();
            $table->integer('num_participantes')->nullable();
            $table->decimal('investimento')->nullable();
            $table->decimal('investimento_associado')->nullable();
            $table->text('observacoes')->nullable();
            $table->string('contato')->nullable();
            $table->string('contato_email')->nullable();
            $table->string('contato_telefone')->nullable();
            $table->string('valor_orcamento')->nullable();
            $table->enum('status_proposta', ['PENDENTE', 'AGUARDANDO APROVACAO', 'APROVADA', 'REPROVADA'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_cursos');
    }
};
