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
        Schema::create('avaliacao_avaliadores', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->integer('agenda_avaliacao_id')->nullable();
            $table->integer('avaliador_id');
            $table->string('empresa')->nullable();
            $table->date('data')->nullable();
            $table->enum('situacao', ['AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LÍDER', 'ESPECIALISTA']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliacao_avaliadores');
    }
};
