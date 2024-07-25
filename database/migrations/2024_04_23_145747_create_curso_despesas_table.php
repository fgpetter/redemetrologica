<?php

use App\Models\AgendaCursos;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Expression;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('curso_despesas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->default(new Expression("(replace(left(uuid(),12),_utf8mb3'-',_utf8mb4'0'))"))->unique();
            $table->foreignIdFor(AgendaCursos::class)->constrained();
            $table->unsignedBigInteger('material_padrao_id');
            $table->decimal('quantidade', 8, 2)->default(0);
            $table->decimal('valor', 8, 2)->default(0);
            $table->decimal('total', 8, 2)->default(0);
            $table->timestamps();

            $table->foreign('material_padrao_id')->references('id')->on('materiais_padroes')->onDelete('cascade');
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
