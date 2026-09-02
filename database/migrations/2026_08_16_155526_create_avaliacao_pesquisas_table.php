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
        Schema::create('avaliacao_pesquisas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('agenda_avaliacao_id')->unique()->constrained('agenda_avaliacoes')->cascadeOnDelete();
            $table->unsignedTinyInteger('nota_1')->nullable();
            $table->unsignedTinyInteger('nota_2')->nullable();
            $table->unsignedTinyInteger('nota_3')->nullable();
            $table->unsignedTinyInteger('nota_4')->nullable();
            $table->unsignedTinyInteger('nota_5')->nullable();
            $table->unsignedTinyInteger('nota_6')->nullable();
            $table->unsignedTinyInteger('nota_7')->nullable();
            $table->text('criterios_harmoniosos')->nullable();
            $table->text('divergencias')->nullable();
            $table->text('pontos_melhoria')->nullable();
            $table->json('comentarios_avaliadores')->nullable();
            $table->string('responsavel', 191)->nullable();
            $table->dateTime('preenchido_em')->nullable();
            $table->boolean('conferida')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliacao_pesquisas');
    }
};
