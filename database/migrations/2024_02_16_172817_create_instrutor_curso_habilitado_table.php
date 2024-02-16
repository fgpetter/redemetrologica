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
        Schema::create('instrutor_curso_habilitado', function (Blueprint $table) {
            $table->uid();
            $table->unsignedBigInteger('instrutor_id');
            $table->unsignedBigInteger('curso_id');
            $table->boolean('habilitado');
            $table->string('conhecimento');
            $table->string('experiencia');
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instrutor_curso_habilitado');
    }
};
