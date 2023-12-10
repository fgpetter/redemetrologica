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
            $table->enum('tipo_agendamento', ['CURSO','EVENTO','IN-COMPANY']);
            $table->foreignId('curso_id');
            $table->string('folder');
            $table->string('link_folder');
            $table->integer('id_empresa');
            $table->text('endereco_local');
            $table->string('sala');
            $table->string('num_reserva');
            $table->date('data_confirmacao');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->date('data_insc_inicio');
            $table->date('data_insc_fim');
            $table->date('data_limite_pesquisa');
            $table->date('data_limite_pagamento');
            $table->string('tipo_data');
            $table->string('horario');
            $table->boolean('inscricoes');
            $table->boolean('site');
            $table->decimal('investimento');
            $table->decimal('investimento_associado');
            $table->integer('carga_horaria');
            $table->text('observacoes');
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
