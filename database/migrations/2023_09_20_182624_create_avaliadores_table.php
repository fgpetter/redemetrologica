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
        Schema::create('avaliadores', function (Blueprint $table) {
            $table->id();
            $table->integer('uid');
            $table->foreignId('pessoa_id')->constrained();
            $table->string('curriculo')->nullable();
            $table->boolean('exp_min_comprovada');
            $table->boolean('curso_incerteza');
            $table->boolean('curso_iso');
            $table->boolean('curso_aud_interna');
            $table->boolean('parecer_psicologico');
            $table->date('data_ingresso');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('avaliadores');
    }
};
