<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('status_avaliador', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->default(new Expression("(replace(left(uuid(),12),_utf8mb3'-',_utf8mb4'0'))"))->unique();
            $table->unsignedBigInteger('avaliador_id');
            $table->date('data')->nullable();
            $table->enum('status', ['ATIVO', 'AVALIADOR', 'AVALIADOR EM TREINAMENTO', 'AVALIADOR LIDER', 'ESPECIALISTA', 'INATIVO'])->nullable();
            $table->boolean('parecer_positivo')->default(0);
            $table->boolean('seminario')->default(0);
            $table->timestamps();

            $table->foreign('avaliador_id')->references('id')->on('avaliadores')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_avaliador');
    }
};
