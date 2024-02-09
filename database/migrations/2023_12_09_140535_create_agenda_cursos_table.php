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
        Schema::create('agenda_cursos', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->enum('status', ['AGENDADO', 'CANCELADO', 'CONFIRMADO', 'REALIZADO', 'PROPOSTA ENVIADA', 'REAGENDAR']);
            $table->boolean('destaque');
            $table->enum('tipo_agendamento', ['ONLINE','EVENTO','IN-COMPANY']);
            $table->foreignId('curso_id');
            //$table->foreignId('instrutor_id');
            $table->integer('id_empresa')->nullable();
            $table->integer('id_endereco')->nullable();
            $table->text('endereco_local')->nullable();
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->date('data_insc_inicio')->nullable();
            $table->date('data_insc_fim')->nullable();
            $table->date('data_limite_pesquisa')->nullable();
            $table->date('data_limite_pagamento')->nullable();
            $table->date('validade_proposta')->nullable();
            $table->date('data_confirmacao')->nullable();
            $table->string('horario')->nullable();
            $table->boolean('inscricoes');
            $table->boolean('site');
            $table->integer('carga_horaria')->nullable();
            $table->integer('num_participantes')->nullable();
            $table->decimal('investimento')->nullable();
            $table->decimal('investimento_associado')->nullable();
            $table->text('observacoes')->nullable();
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
