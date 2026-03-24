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
        if (Schema::hasTable('interlab_rodada_parametros') && Schema::hasColumn('interlab_rodada_parametros', 'parametro_id')) {
            Schema::table('interlab_rodada_parametros', function (Blueprint $table) {
                $table->dropForeign(['parametro_id']);
                $table->dropColumn('parametro_id');
            });
        }

        Schema::dropIfExists('parametros');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('parametros')) {
            Schema::create('parametros', function (Blueprint $table) {
                $table->id();
                $table->string('uid')->unique();
                $table->string('descricao');
                $table->timestamps();
            });
        }

        if (Schema::hasTable('interlab_rodada_parametros') && ! Schema::hasColumn('interlab_rodada_parametros', 'parametro_id')) {
            Schema::table('interlab_rodada_parametros', function (Blueprint $table) {
                $table->foreignId('parametro_id')->after('interlab_rodada_id')->constrained('parametros')->onDelete('cascade');
            });
        }
    }
};
