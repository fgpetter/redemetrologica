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
            $table->string('fornecedor')->nullable();
            $table->string('fabricante')->nullable();
            $table->string('cod_fabricante')->nullable();
            $table->string('marca')->nullable();
            $table->enum('tipo', ['CURSOS', 'INTERLAB', 'AMBOS']);
            $table->boolean('padrao');
            $table->decimal('valor')->nullable();
            $table->enum('tipo_despesa', ['FIXO', 'VARIAVEL', 'OUTROS']);
            $table->text('observacoes')->nullable();

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