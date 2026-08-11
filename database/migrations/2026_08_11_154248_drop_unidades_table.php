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
        if (Schema::hasTable('enderecos') && Schema::hasColumn('enderecos', 'unidade_id')) {
            Schema::table('enderecos', function (Blueprint $table) {
                $table->dropColumn('unidade_id');
            });
        }

        if (Schema::hasTable('notas_fiscais') && Schema::hasColumn('notas_fiscais', 'unidade_id')) {
            Schema::table('notas_fiscais', function (Blueprint $table) {
                $table->dropColumn('unidade_id');
            });
        }

        Schema::dropIfExists('unidades');
    }

    /**
     * Reverse the migrations.
     *
     * Intencionalmente irreversível: Unidade foi removida do domínio.
     */
    public function down(): void
    {
        //
    }
};
