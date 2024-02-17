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
        Schema::create('curso_inscrito_empresas', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->foreignId('agenda_curso_id');
            $table->foreignId('pessoa_id');
            $table->string('como_ficou_sabendo')->nullable();
            $table->boolean('associado')->default(false);
            $table->boolean('emite_nf')->default(false);
            $table->integer('nf_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_inscrito_empresas');
    }
};
