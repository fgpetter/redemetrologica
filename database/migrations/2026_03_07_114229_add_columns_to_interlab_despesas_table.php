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
        // antes de rodar a migration, limpar a tabela interlab_despesas
        DB::table('interlab_despesas')->truncate();

        // rodar a migration
        Schema::table('interlab_despesas', function (Blueprint $table) {
            $table->dropForeign(['material_padrao_id']);
            $table->dropColumn('material_padrao_id');
            $table->dropColumn('fornecedor');
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->after('agenda_interlab_id');
            $table->string('material_servico')->nullable()->after('fornecedor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
