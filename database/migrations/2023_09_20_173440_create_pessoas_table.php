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
        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            $table->string('nome_razao');
            $table->string('nome_fantasia')->nullable();
            $table->string('cpf_cnpj');
            $table->enum('tipo_pessoa', ['PF', 'PJ']);
            $table->string('rg_ie');
            $table->string('insc_municipal');
            $table->integer('celular')->nullable();
            $table->string('email')->nullable();
            $table->string('codigo_contabil');
            $table->integer('contato_cobranca')->nullable();
            $table->integer('alterado_por');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pessoas');
    }
};
