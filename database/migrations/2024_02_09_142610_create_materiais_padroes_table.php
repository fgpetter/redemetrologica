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
        Schema::create('materiais_padroes', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('descricao');
            $table->string('cod_fabricante');
            $table->string('fabricante');
            $table->string('marca');
            $table->enum('tipo', ['CURSOS', 'INTERLAB', 'AMBOS']);
            $table->boolean('padrao');
            $table->double('valor');
            $table->enum('tipo_despesa', ['FIXO', 'VARIAVEL', 'OUTROS']);
            $table->text('observacoes');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materiais_padroes');
    }
};
