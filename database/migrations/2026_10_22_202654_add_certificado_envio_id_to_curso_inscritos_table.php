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
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->uuid('certificado_envio_id')->nullable()->after('certificado_emitido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('curso_inscritos', function (Blueprint $table) {
            $table->dropColumn('certificado_envio_id');
        });
    }
};
