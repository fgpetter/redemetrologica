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
        Schema::create('avaliador_areas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->unsignedBigInteger('avaliador_id');
            $table->unsignedBigInteger('area_id');
            $table->date('data_cadastro')->nullable();
            $table->enum('situacao', ['ATIVO', 'AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LIDER', 'ESPECIALISTA', 'INATIVO'])->nullable();
            $table->timestamps();

            $table->foreign('avaliador_id')->references('id')->on('avaliadores')->onDelete('cascade');
            $table->foreign('area_id')->references('id')->on('areas_atuacao')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliador_areas');
    }
};
