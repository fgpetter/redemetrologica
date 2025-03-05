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
        Schema::create('lancamentos_financeiros', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->date('data_emissao')->nullable();
            $table->boolean('enviado_banco')->default(0);
            $table->string('consiliacao')->nullable();
            $table->string('documento')->nullable();
            $table->string('nota_fiscal')->nullable();
            $table->foreignId('pessoa_id')->nullable();
            $table->foreignId('centro_custo_id')->nullable();
            $table->foreignId('plano_conta_id')->nullable();
            $table->string('historico', 999)->nullable();
            $table->enum('tipo_lancamento', ['CREDITO', 'DEBITO']);
            $table->decimal('valor')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->foreignId('modalidade_pagamento_id')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['EFETIVADO', 'PROVISIONADO']);
            $table->text('observacoes')->nullable();
            $table->foreignId('agenda_curso_id')->nullable();
            $table->foreignId('agenda_interlab_id')->nullable();
            $table->foreignId('agenda_avaliacao_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lancamentos_financeiros');
    }
};
