<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agenda_curso_arquivos', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('agenda_curso_id')->constrained('agenda_cursos');
            $table->string('arquivo');
            $table->string('descricao', 190)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agenda_curso_arquivos');
    }
};
