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
        Schema::create('curso_despesas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agenda_curso_id');
            $table->foreignId('material_padrao_id');
            $table->decimal('quantidade', 8, 2)->default(0);
            $table->decimal('valor', 8, 2)->default(0);
            $table->decimal('total', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curso_despesas');
    }
};
