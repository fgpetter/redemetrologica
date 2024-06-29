<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agenda_avaliacoes', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->default(new Expression("(replace(left(uuid(),12),_utf8mb3'-',_utf8mb4'0'))"));
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->foreignId('laboratorio_id')->constrained();
            $table->text('contato')->nullable();
            $table->unsignedBigInteger('tipo_avaliacao_id')->nullable();
            $table->boolean('fr_28')->default(0);
            $table->decimal('valor_proposta')->nullable();
            $table->enum('status_proposta', ['PENDENTE','AGUARDANDO', 'APROVADA', 'REPROVADA'])->default('PENDENTE');
            $table->enum('carta_marcacao', ['INCOMPLETA', 'COMPLETA','ENVIADA TODOS','ENVIADA AVALIADORES', 'ENVIADA LABORATORIO', 'NAO ENVIADA COMPLETA', 'APROVADA TODOS', 'APROVADA AVALIADORES', 'NAO APROVADA'])->nullable();
            $table->boolean('inf_avaliadores')->default(0);
            $table->date('data_proc_laboratorio')->nullable();
            $table->boolean('fr_41', 3)->default(0);
            $table->boolean('fr_101', 3)->default(0);
            $table->boolean('fr_48', 3)->default(0);
            $table->boolean('carta_reconhecimento', 3)->default(0);
            $table->date('pesq_satisfacao')->nullable();
            $table->date('data_proposta_acoes_corretivas')->nullable();
            $table->date('data_acoes_corretivas')->nullable();
            $table->enum('acoes_aceitas', ['NÃO', 'SIM', 'PARCIALMENTE'])->nullable();
            $table->enum('comite', ['APROVADO', 'NAO APROVADO', 'COM PENDENCIAS'])->nullable();
            $table->date('prazo_ajuste_pos_comite')->nullable();
            $table->boolean('proc_laboratorio')->default(0);
            $table->enum('relatorio_fr06', ['INCOMPLETA', 'COMPLETA','ENVIADA TODOS','ENVIADA AVALIADORES', 'ENVIADA LABORATORIO', 'NAO ENVIADA COMPLETA', 'APROVADA TODOS', 'APROVADA AVALIADORES', 'NAO APROVADA'])->nullable();
            $table->date('retorno_fr06')->nullable();
            $table->date('data_reuniao_comite')->nullable();
            $table->date('validade_certificado')->nullable();
            $table->date('data_publicacao_site')->nullable();
            $table->boolean('certificado')->default(0);
            $table->enum('enviado_certificado', ['ENVIADO', 'NAO ENVIADO', 'PENDENTE'])->nullable();
            $table->boolean('certificado_impresso')->default(0);
            $table->date('ano_revisao_certificado')->nullable();
            $table->integer('num_ensaios')->nullable();
            $table->decimal('soma_avaliadores')->nullable();
            $table->decimal('soma_despesas_estimadas')->nullable();
            $table->decimal('soma_despesas_reais')->nullable();
            $table->decimal('perc_lucro')->nullable();
            $table->decimal('nf')->nullable();
            $table->decimal('superavit')->nullable();
            $table->date('data_envio_proposta')->nullable();
            $table->integer('num_aval_treinamento')->nullable();
            $table->unsignedBigInteger('laboratorio_interno_id')->nullable();
            $table->text('observacoes_orcamento')->nullable();
            $table->text('obs')->nullable();
            $table->timestamps();
            
            $table->foreign('tipo_avaliacao_id')->references('id')->on('tipo_avaliacoes')->onDelete('cascade');
            $table->foreign('laboratorio_interno_id')->references('id')->on('laboratorios_internos')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agenda_avaliacoes');
    }
};
