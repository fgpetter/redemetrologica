<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Adicionar novas FKs nas tabelas que possuem endereço
        Schema::table('pessoas', function (Blueprint $table) {
            $table->foreignId('endereco_id')->nullable()->after('updated_at')->constrained('enderecos')->nullOnDelete();
            $table->foreignId('endereco_cobranca_id')->nullable()->after('endereco_id')->constrained('enderecos')->nullOnDelete();
        });

        Schema::table('unidades', function (Blueprint $table) {
            $table->foreignId('endereco_id')->nullable()->after('updated_at')->constrained('enderecos')->nullOnDelete();
        });

        Schema::table('avaliadores', function (Blueprint $table) {
            $table->foreignId('endereco_comercial_id')->nullable()->after('updated_at')->constrained('enderecos')->nullOnDelete();
            $table->foreignId('endereco_pessoal_id')->nullable()->after('endereco_comercial_id')->constrained('enderecos')->nullOnDelete();
        });

        // 2. Migrar dados existentes

        // pessoas.endereco_id <- pessoas.end_padrao (já aponta para enderecos.id)
        DB::statement('UPDATE pessoas SET endereco_id = end_padrao WHERE end_padrao IS NOT NULL');

        // pessoas.endereco_cobranca_id <- pessoas.end_cobranca
        DB::statement('UPDATE pessoas SET endereco_cobranca_id = end_cobranca WHERE end_cobranca IS NOT NULL');

        // unidades.endereco_id <- enderecos.id WHERE enderecos.unidade_id = unidades.id
        DB::statement('
            UPDATE unidades u
            INNER JOIN enderecos e ON e.unidade_id = u.id
            SET u.endereco_id = e.id
        ');

        // avaliadores.endereco_comercial_id <- enderecos.id WHERE enderecos.avaliador_id = avaliadores.id
        DB::statement('
            UPDATE avaliadores a
            INNER JOIN enderecos e ON e.avaliador_id = a.id
            SET a.endereco_comercial_id = e.id
        ');

        // avaliadores.endereco_pessoal_id <- enderecos.id WHERE pessoa_id = avaliador.pessoa_id AND avaliador_id IS NULL AND unidade_id IS NULL
        DB::statement('
            UPDATE avaliadores a
            INNER JOIN enderecos e ON e.pessoa_id = a.pessoa_id AND e.avaliador_id IS NULL AND e.unidade_id IS NULL
            SET a.endereco_pessoal_id = e.id
        ');

        // 3. Remover colunas antigas da tabela enderecos
        Schema::table('enderecos', function (Blueprint $table) {
            $table->dropForeign(['pessoa_id']);
            $table->dropForeign(['avaliador_id']);
            $table->dropColumn(['pessoa_id', 'unidade_id', 'avaliador_id', 'cobranca', 'email']);
        });

        // 4. Remover colunas antigas da tabela pessoas
        Schema::table('pessoas', function (Blueprint $table) {
            $table->dropColumn(['end_padrao', 'end_cobranca']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Restaurar colunas na tabela pessoas
        Schema::table('pessoas', function (Blueprint $table) {
            $table->integer('end_padrao')->nullable()->after('site');
            $table->integer('end_cobranca')->nullable()->after('end_padrao');
        });

        // 2. Restaurar colunas na tabela enderecos
        Schema::table('enderecos', function (Blueprint $table) {
            $table->integer('unidade_id')->nullable()->after('uid');
            $table->foreignId('pessoa_id')->after('unidade_id')->constrained()->cascadeOnDelete();
            $table->foreignId('avaliador_id')->nullable()->after('updated_at')->constrained('avaliadores')->cascadeOnDelete();
            $table->boolean('cobranca')->default(false)->after('uf');
            $table->string('email', 50)->nullable()->after('uf');
        });

        // 3. Migrar dados de volta
        DB::statement('UPDATE pessoas SET end_padrao = endereco_id WHERE endereco_id IS NOT NULL');
        DB::statement('UPDATE pessoas SET end_cobranca = endereco_cobranca_id WHERE endereco_cobranca_id IS NOT NULL');

        DB::statement('
            UPDATE enderecos e
            INNER JOIN unidades u ON u.endereco_id = e.id
            SET e.unidade_id = u.id
        ');

        DB::statement('
            UPDATE enderecos e
            INNER JOIN avaliadores a ON a.endereco_comercial_id = e.id
            SET e.avaliador_id = a.id
        ');

        // 4. Remover novas FKs
        Schema::table('avaliadores', function (Blueprint $table) {
            $table->dropForeign(['endereco_comercial_id']);
            $table->dropForeign(['endereco_pessoal_id']);
            $table->dropColumn(['endereco_comercial_id', 'endereco_pessoal_id']);
        });

        Schema::table('unidades', function (Blueprint $table) {
            $table->dropForeign(['endereco_id']);
            $table->dropColumn('endereco_id');
        });

        Schema::table('pessoas', function (Blueprint $table) {
            $table->dropForeign(['endereco_id']);
            $table->dropForeign(['endereco_cobranca_id']);
            $table->dropColumn(['endereco_id', 'endereco_cobranca_id']);
        });
    }
};
