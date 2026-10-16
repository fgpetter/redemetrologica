<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suporte_controle', function (Blueprint $table): void {
            $table->unsignedInteger('id')->primary();
        });
        DB::table('suporte_controle')->insert(['id' => 1]);

        Schema::create('suporte_demandas', function (Blueprint $table): void {
            $table->id();
            $table->string('uid')->unique();
            $table->string('titulo', 255);
            $table->longText('descricao');
            $table->string('status', 30)->default('aberta');
            $table->foreignId('autor_id')->constrained('users')->restrictOnDelete();
            $table->unsignedBigInteger('prioridade')->index();
            $table->unsignedBigInteger('pendencia_versao')->default(0);
            $table->unsignedBigInteger('resolvida_versao')->default(0);
            $table->foreignId('resolvido_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolvido_em')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedTinyInteger('em_andamento_unico')->nullable()
                ->virtualAs("CASE WHEN status = 'em_andamento' AND deleted_at IS NULL THEN 1 ELSE NULL END")
                ->unique();
        });
        Schema::create('suporte_atividades', function (Blueprint $table): void {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('demanda_id')->constrained('suporte_demandas')->restrictOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('suporte_atividades')->restrictOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();
            $table->longText('descricao');
            $table->boolean('apos_encerramento')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['demanda_id', 'parent_id', 'created_at']);
        });
        Schema::create('suporte_demanda_user', function (Blueprint $table): void {
            $table->foreignId('demanda_id')->constrained('suporte_demandas')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['demanda_id', 'user_id']);
        });
        Schema::create('suporte_arquivos', function (Blueprint $table): void {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignId('demanda_id')->nullable()->constrained('suporte_demandas')->restrictOnDelete();
            $table->foreignId('atividade_id')->nullable()->constrained('suporte_atividades')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->uuid('rascunho')->nullable()->index();
            $table->string('tipo', 10);
            $table->string('nome', 255);
            $table->string('caminho', 255);
            $table->string('mime');
            $table->unsignedBigInteger('tamanho');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suporte_arquivos');
        Schema::dropIfExists('suporte_demanda_user');
        Schema::dropIfExists('suporte_atividades');
        Schema::dropIfExists('suporte_demandas');
        Schema::dropIfExists('suporte_controle');
    }
};
