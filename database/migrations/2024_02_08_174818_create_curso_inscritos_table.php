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
        Schema::create('curso_inscritos', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->foreignId('pessoa_id')->constrained();
            $table->foreignId('curso_id')->constrained();
            $table->decimal('valor')->nullable();
            $table->boolean('confirmou');
            $table->dateTime('certificado_emitido')->nullable();
            $table->foreignId('pesquisa_id')->nullable();
            $table->foreignId('nf_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_inscritos');
    }
};