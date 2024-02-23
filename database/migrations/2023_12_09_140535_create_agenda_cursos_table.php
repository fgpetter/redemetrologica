<?php

use App\Models\Instrutor;
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
        Schema::create('agenda_cursos', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->enum('status', ['AGENDADO', 'CANCELADO', 'CONFIRMADO', 'REALIZADO', 'PROPOSTA ENVIADA', 'REAGENDAR']);
            $table->boolean('destaque')->default(0);
            $table->enum('tipo_agendamento', ['ONLINE', 'EVENTO', 'IN-COMPANY']);
            $table->foreignId('curso_id');
            $table->foreignIdFor(Instrutor::class);
            $table->foreignId('pessoa_id')->nullable();
            $table->text('endereco_local')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->date('data_limite_pagamento')->nullable();
            $table->date('validade_proposta')->nullable();
            $table->string('horario')->nullable();
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
            $table->string('thumb')->nullable();
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
