<?php

use App\Models\AgendaInterlab;
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
        Schema::create('interlab_despesas', function (Blueprint $table) {
            $table->id();
            $table->string('uid')->unique();
            $table->foreignIdFor(AgendaInterlab::class)->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('material_padrao_id'); // FK para materiais_padroes
            $table->string('fornecedor')->nullable();
            $table->string('fabricante')->nullable();
            $table->string('cod_fabricante')->nullable();
            $table->string('marca')->nullable();
            $table->string('lote')->nullable();
            $table->decimal('quantidade', 8, 2)->nullable();
            $table->decimal('valor', 8, 2)->nullable();
            $table->decimal('total', 8, 2)->nullable();
            $table->date('validade')->nullable();
            $table->date('data_compra')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // criado separado por questao de plural
            $table->foreign('material_padrao_id')->references('id')->on('materiais_padroes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interlab_despesas');
    }
};
