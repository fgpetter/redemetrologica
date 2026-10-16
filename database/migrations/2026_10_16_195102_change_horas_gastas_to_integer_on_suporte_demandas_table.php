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
        if (DB::table('suporte_demandas')->whereRaw('horas_gastas <> FLOOR(horas_gastas)')->exists()) {
            throw new RuntimeException('Existem horas fracionadas no suporte. Ajuste os valores antes de converter a coluna para inteiro.');
        }

        Schema::table('suporte_demandas', function (Blueprint $table) {
            $table->unsignedInteger('horas_gastas')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suporte_demandas', function (Blueprint $table) {
            $table->decimal('horas_gastas', 8, 2)->default(0)->change();
        });
    }
};
