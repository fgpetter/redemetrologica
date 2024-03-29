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
            $table->string('uid');
            $table->string('nome_razao');
            $table->string('nome_fantasia')->nullable();
            $table->string('cpf_cnpj');
            $table->enum('tipo_pessoa', ['PF', 'PJ']);
            $table->string('rg_ie')->nullable();
            $table->string('insc_municipal')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->integer('alterado_por')->nullable();
            $table->integer('end_padrao')->nullable();
            $table->integer('end_cobranca')->nullable();
            $table->boolean('associado')->default(0);
            $table->softDeletes();
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
