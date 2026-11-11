<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['agenda_curso_materiais', 'curso_materiais'] as $table) {
            if (Schema::hasTable($table) && DB::table($table)->exists()) {
                throw new RuntimeException("Tabela {$table} não está vazia; execute cursos:limpar-materiais-legados antes da migration.");
            }
        }

        Schema::dropIfExists('agenda_curso_materiais');
        Schema::dropIfExists('curso_materiais');
    }

    public function down(): void
    {
        if (! Schema::hasTable('curso_materiais')) {
            Schema::create('curso_materiais', function (Blueprint $table) {
                $table->id();
                $table->string('uid')->unique();
                $table->foreignId('curso_id');
                $table->string('arquivo');
                $table->string('descricao')->nullable();
                $table->timestamps();
            });
        }
        if (! Schema::hasTable('agenda_curso_materiais')) {
            Schema::create('agenda_curso_materiais', function (Blueprint $table) {
                $table->unsignedBigInteger('agenda_curso_id');
                $table->unsignedBigInteger('curso_material_id');
            });
        }
    }
};
