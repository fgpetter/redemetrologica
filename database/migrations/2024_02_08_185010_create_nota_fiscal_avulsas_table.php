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
        Schema::create('notas_fiscais_avulsas', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('cpf_cnpj');
            $table->string('rg_ie');
            $table->string('insc_munic');
            $table->string('nome_razao');
            $table->string('endereco')->nullable();
            $table->string('complemento')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->string('cidade')->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('telefone');
            $table->string('email');
            $table->enum('modalidade', ['CHEQUE', 'BBPAG', 'BOLETO', 'ACERTO', 'EMPENHO', 'ORDEM DE COMPRA', 'DEBITO']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_fiscais_avulsas');
    }
};
