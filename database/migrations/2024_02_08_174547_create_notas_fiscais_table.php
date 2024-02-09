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
        Schema::create('notas_fiscais', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('num_nf');
            $table->foreignId('pessoa_id')->constrained();
            $table->foreignId('unidade_id');
            $table->integer('evento_id');
            $table->enum('evento', ['CURSO', 'AVALIACAO', 'INTERLAB']);
            $table->decimal('valor');
            $table->date('data_pgto');
            $table->date('data_doc');
            $table->boolean('enviado_financeiro');
            $table->enum('modalidade', ['CHEQUE', 'BBPAG', 'BOLETO', 'ACERTO', 'EMPENHO', 'ORDEM DE COMPRA', 'DEBITO']);
            $table->text('observacoes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais');
    }
};
