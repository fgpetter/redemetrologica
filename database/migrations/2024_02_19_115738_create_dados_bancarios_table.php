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
        Schema::create('dados_bancarios', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('pessoa_id')->constrained();
            $table->string('nome_conta')->nullable();
            $table->string('nome_banco');
            $table->string('cod_banco')->nullable();
            $table->string('agencia');
            $table->string('conta');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dados_bancarios');
    }
};
