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

        // 2a. pessoas.endereco_id <- end_padrao (onde existir)
        DB::statement('UPDATE pessoas SET endereco_id = end_padrao WHERE end_padrao IS NOT NULL');

        // 2b. pessoas.endereco_id <- endereço mais recente não-cobrança (onde end_padrao era NULL)
        DB::statement('
            UPDATE pessoas p
            INNER JOIN (
                SELECT pessoa_id, MAX(id) as eid
                FROM enderecos
                WHERE (cobranca = 0 OR cobranca IS NULL)
                AND pessoa_id IS NOT NULL
                GROUP BY pessoa_id
            ) e ON e.pessoa_id = p.id
            SET p.endereco_id = e.eid
            WHERE p.endereco_id IS NULL
        ');

        // 2c. pessoas sem endereço ainda — usar qualquer endereço disponível (mesmo cobrança)
        DB::statement('
            UPDATE pessoas p
            INNER JOIN (
                SELECT pessoa_id, MAX(id) as eid
                FROM enderecos
                WHERE pessoa_id IS NOT NULL
                GROUP BY pessoa_id
            ) e ON e.pessoa_id = p.id
            SET p.endereco_id = e.eid
            WHERE p.endereco_id IS NULL
        ');

        // 2d. pessoas.endereco_cobranca_id <- end_cobranca (onde existir)
        DB::statement('UPDATE pessoas SET endereco_cobranca_id = end_cobranca WHERE end_cobranca IS NOT NULL');

        // 2e. pessoas sem endereco_cobranca_id — usar endereço com flag cobranca=1
        DB::statement('
            UPDATE pessoas p
            INNER JOIN (
                SELECT pessoa_id, MAX(id) as eid
                FROM enderecos
                WHERE cobranca = 1
                AND pessoa_id IS NOT NULL
                GROUP BY pessoa_id
            ) e ON e.pessoa_id = p.id
            SET p.endereco_cobranca_id = e.eid
            WHERE p.endereco_cobranca_id IS NULL
        ');

        // 2f. unidades.endereco_id
        DB::statement('
            UPDATE unidades u
            INNER JOIN enderecos e ON e.unidade_id = u.id
            SET u.endereco_id = e.id
        ');

        // 2g. avaliadores.endereco_comercial_id
        DB::statement('
            UPDATE avaliadores a
            INNER JOIN enderecos e ON e.avaliador_id = a.id
            SET a.endereco_comercial_id = e.id
        ');

        // 2h. avaliadores.endereco_pessoal_id
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
            $table->foreignId('pessoa_id')->nullable()->after('unidade_id')->constrained()->nullOnDelete();
            $table->foreignId('avaliador_id')->nullable()->after('updated_at')->constrained('avaliadores')->cascadeOnDelete();
            $table->boolean('cobranca')->default(false)->after('uf');
            $table->string('email', 50)->nullable()->after('uf');
        });

        // 3. Migrar dados de volta
        DB::statement('UPDATE pessoas SET end_padrao = endereco_id WHERE endereco_id IS NOT NULL');
        DB::statement('UPDATE pessoas SET end_cobranca = endereco_cobranca_id WHERE endereco_cobranca_id IS NOT NULL');

        // Restaurar pessoa_id nos enderecos a partir de pessoas.endereco_id
        DB::statement('
            UPDATE enderecos e
            INNER JOIN pessoas p ON p.endereco_id = e.id
            SET e.pessoa_id = p.id
        ');

        // Restaurar pessoa_id nos enderecos de cobrança
        DB::statement('
            UPDATE enderecos e
            INNER JOIN pessoas p ON p.endereco_cobranca_id = e.id
            SET e.pessoa_id = p.id
            WHERE e.pessoa_id IS NULL
        ');

        // Restaurar cobranca flag
        DB::statement('
            UPDATE enderecos e
            INNER JOIN pessoas p ON p.endereco_cobranca_id = e.id
            SET e.cobranca = 1
        ');

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
