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
        Schema::table('suporte_demandas', function (Blueprint $table) {
            $table->string('classificacao', 20)->nullable();
            $table->decimal('horas_gastas', 8, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suporte_demandas', function (Blueprint $table) {
            $table->dropColumn(['classificacao', 'horas_gastas']);
        });
    }
};
