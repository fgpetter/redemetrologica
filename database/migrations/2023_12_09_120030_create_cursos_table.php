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
        Schema::create('cursos', function (Blueprint $table) {
            $table->id();
            $table->string('uid');
            $table->string('descricao')->nullable();
            $table->integer('carga_horaria')->default(0);
            $table->text('objetivo')->nullable();
            $table->text('publico_alvo')->nullable();
            $table->text('pre_requisitos')->nullable();
            $table->text('exemplos_praticos')->nullable();
            $table->text('referencias_utilizadas')->nullable();
            $table->text('conteudo_programatico')->nullable();
            $table->text('observacoes_internas')->nullable();
            $table->enum('tipo_curso', ['OFICIAL','SUPLENTE','OUTROS']);
            $table->string('folder')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cursos');
    }
};
