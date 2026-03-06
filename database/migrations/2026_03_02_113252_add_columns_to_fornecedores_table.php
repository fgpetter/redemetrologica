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
        Schema::table('fornecedores', function (Blueprint $table) {
            $table->json('fornecedor_area')->nullable()->after('observacoes'); // areas de atuação conforme setores da empresa [interlaboratorial, curso, avaliacao]
            $table->string('fornecedor_area_atuacao')->nullable()->after('fornecedor_area'); // descrição da atuação do forncedor
            $table->string('pessoa_contato')->nullable()->after('fornecedor_area_atuacao');
            $table->string('pessoa_contato_email')->nullable()->after('pessoa_contato');
            $table->date('fornecerdor_desde')->nullable()->after('pessoa_contato_email'); // data de inicio da atuação do fornecedor
            $table->boolean('ativo')->default(1)->after('fornecerdor_desde');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fornecedores', function (Blueprint $table) {
            $table->dropColumn('fornecedor_area');
            $table->dropColumn('fornecedor_area_atuacao');
            $table->dropColumn('pessoa_contato');
            $table->dropColumn('pessoa_contato_email');
            $table->dropColumn('fornecerdor_desde');
        });
    }
};
