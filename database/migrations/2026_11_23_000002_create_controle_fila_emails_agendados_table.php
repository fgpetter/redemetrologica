<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('controle_fila_emails_agendados', function (Blueprint $table): void {
            $table->unsignedTinyInteger('id')->primary();
            $table->unsignedBigInteger('disponivel_em')->default(0);
            $table->unsignedBigInteger('job_id')->nullable();
            $table->unsignedTinyInteger('tentativa')->nullable();
        });

        DB::table('controle_fila_emails_agendados')->insert(['id' => 1]);
    }

    public function down(): void
    {
        Schema::dropIfExists('controle_fila_emails_agendados');
    }
};
