<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_interlabs', function (Blueprint $table) {
            $table->dropColumn('certificado');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_interlabs', function (Blueprint $table) {
            $table->enum('certificado', ['EMPRESA', 'PARTICIPANTE'])->nullable()->after('status');
        });
    }
};
