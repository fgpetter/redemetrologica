<?php

use App\Models\Interlab;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('interlabs', function (Blueprint $table) {
            $table->enum('avaliacao', ['LABORATORIAL', 'ANALISTA'])->nullable()->after('tipo');
        });

        Interlab::whereNull('avaliacao')->update(['avaliacao' => 'LABORATORIAL']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interlabs', function (Blueprint $table) {
            $table->dropColumn('avaliacao');
        });
    }
};
