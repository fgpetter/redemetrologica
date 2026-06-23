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
        Schema::dropIfExists('convites');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('convites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pessoa_id')->constrained();
            $table->foreignId('empresa_id')->nullable()->constrained();
            $table->foreignId('agenda_curso_id')->nullable()->constrained();
            $table->foreignId('agenda_interlab_id')->nullable()->constrained();
            $table->string('nome');
            $table->string('email');
            $table->enum('status', ['ENVIAR','PENDENTE','REGISTRADO'])->default('ENVIAR');
            $table->dateTime('enviado')->nullable();
            $table->timestamps();
        });
    }
};
