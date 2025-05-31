<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pessoas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nome_razao');
            $table->string('nome_fantasia')->nullable();
            $table->string('cpf_cnpj')->unique();
            $table->enum('tipo_pessoa', ['PF', 'PJ']);
            $table->string('rg_ie')->nullable();
            $table->string('insc_municipal')->nullable();
            $table->string('telefone')->nullable();
            $table->string('telefone_alt')->nullable();
            $table->string('celular')->nullable();
            $table->string('email')->nullable();
            $table->string('email_cobranca')->nullable();
            $table->string('site')->nullable();
            $table->integer('end_padrao')->nullable();
            $table->integer('end_cobranca')->nullable();
            $table->boolean('associado')->default(0);
            $table->text('observacoes')->nullable();
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
