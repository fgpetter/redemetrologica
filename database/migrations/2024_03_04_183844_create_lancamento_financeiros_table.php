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
        Schema::create('lancamentos_financeiros', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->date('data_emissao')->nullable();
            $table->boolean('enviado_banco')->default(0);
            $table->string('num_documento')->nullable();
            $table->string('documento')->nullable();
            $table->foreignId('pessoa_id')->nullable();
            $table->foreignId('centro_custo_id')->nullable();
            $table->string('historico')->nullable();
            $table->enum('tipo_lancamento', ['CREDITO', 'DEBITO']);
            $table->decimal('valor')->nullable();
            $table->date('data_vencimento')->nullable();
            $table->foreignId('modalidade_pagamento_id')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['EFETIVADO', 'PROVISIONADO']);
            $table->text('observacoes')->nullable();
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