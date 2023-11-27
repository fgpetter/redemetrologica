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
        Schema::create('dados_bancarios', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->foreignId('pessoa_id')->constrained()->cascadeOnDelete();
            $table->string('nome_conta')->nullable() ;
            $table->string('nome_banco');
            $table->string('cod_banco');
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
        Schema::dropIfExists('dado_bancarios');
    }
};
