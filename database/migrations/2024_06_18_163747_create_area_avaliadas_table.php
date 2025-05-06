<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('areas_avaliadas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('area_atuacao_id');
            $table->unsignedBigInteger('avaliacao_id');
            $table->unsignedBigInteger('avaliador_id');
            $table->enum('situacao', ['ATIVO', 'AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LIDER', 'ESPECIALISTA', 'INATIVO'])->nullable();
            $table->integer('num_ensaios')->nullable();
            $table->date('data_inicial')->nullable();
            $table->date('data_final')->nullable();
            $table->integer('dias')->nullable(); //dias deve ser int
            $table->decimal('valor_dia')->nullable();
            $table->decimal('valor_lider')->nullable();
            $table->decimal('valor_avaliador')->nullable();
            $table->decimal('valor_estim_desloc')->nullable();
            $table->decimal('valor_real_desloc')->nullable();
            $table->decimal('valor_estim_alim')->nullable();
            $table->decimal('valor_real_alim')->nullable();
            $table->decimal('valor_estim_hosped')->nullable();
            $table->decimal('valor_real_hosped')->nullable();
            $table->decimal('valor_estim_extras')->nullable();
            $table->decimal('valor_real_extras')->nullable();
            $table->decimal('total_gastos_estim')->nullable();
            $table->decimal('total_gastos_reais')->nullable();
            $table->decimal('valor_avaliador_despesas')->nullable();
            $table->decimal('total_avaliador_despesas_reais')->nullable();
            $table->timestamps();

            $table->foreign('area_atuacao_id')->references('id')->on('areas_atuacao')->onDelete('cascade');
            $table->foreign('avaliacao_id')->references('id')->on('agenda_avaliacoes')->onDelete('cascade');
            $table->foreign('avaliador_id')->references('id')->on('avaliadores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('areas_avaliadas');
    }
};
